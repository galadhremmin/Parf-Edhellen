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
            Propose a change for gloss &ldquo;{props.gloss.word}&rdquo;
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
            store: undefined,
            url: `/dashboard/contribution/create/gloss?entity_id=${props.gloss.id}`
        };
    }

    componentWillMount() {
        EDAPI.get(this.state.url, {
            // It is necessary to provide these additional headers to ensure a JSON response.
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(this.onReceiveData.bind(this));
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
                <EDGlossForm admin={false} confirmButtonText={'Propose changes'} />
            </Provider>
            <hr />
            <p>
                You can alternatively <a href={this.state.url}>propose the changes to the gloss on the dashboard</a>.
            </p>
        </div>;
    }
}

export default EDEditComponentFactory;
