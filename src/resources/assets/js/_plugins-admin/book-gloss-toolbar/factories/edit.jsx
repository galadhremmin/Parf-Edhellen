import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import EDAPI from 'ed-api';
import thunkMiddleware from 'redux-thunk';
import { EDComponentFactory } from 'ed-components/dialog';
import EDGlossAdminReducer from '../../../gloss/reducers/admin';
import EDGlossForm from '../../../gloss/components/forms';

class EDEditComponentFactory extends EDComponentFactory {
    get titleComponent() {
        return props => <span>
            <span className="glyphicon glyphicon-trash" />
            {' '}
            Edit gloss &ldquo;{props.gloss.word}&rdquo;
        </span>;
    }

    get bodyComponent() {
        return props => <BodyComponent {...props} onSubmit={this.onSubmit.bind(this, props.gloss)} />;
    }

    onSubmit(gloss) {
        // nooop -- should automatically redirect.
    }
}

class BodyComponent extends React.Component {
    constructor(props, context) {
        super(props, context);

        this.state = {
            store: undefined
        };
    }

    componentWillMount() {
        EDAPI.get(`/admin/gloss/${this.props.gloss.id}/edit`).then(this.onReceiveData.bind(this));
    }
    
    onReceiveData(resp) {
        const store = createStore(EDGlossAdminReducer, resp.data,
            applyMiddleware(thunkMiddleware)
        );

        this.setState({
            store
        });
    }

    render() {
        if (! this.state.store) {
            return null;
        }

        return <div>
            <Provider store={this.state.store}>
                <EDGlossForm admin={true} />
            </Provider>
            <hr />
            <p>
                You can alternatively <a href={`/admin/gloss/${this.props.gloss.id}/edit`}>edit the gloss on the dashboard</a>.
            </p>
        </div>;
    }
}

export default EDEditComponentFactory;
