import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import axios from 'axios';
import thunkMiddleware from 'redux-thunk';
import EDConfig from 'ed-config';
import { EDComponentFactory } from 'ed-components/dialog';
import EDTranslationAdminReducer from '../../../translation/reducers/admin';
import EDTranslationForm from '../../../translation/components/forms';

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
        axios.get(`/admin/translation/${this.props.gloss.id}/edit`, {
            // It is necessary to provide these additional headers to ensure a JSON response.
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(this.onReceiveData.bind(this));
    }
    
    onReceiveData(resp) {
        const store = createStore(EDTranslationAdminReducer, resp.data,
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
                <EDTranslationForm admin={true} />
            </Provider>
            <hr />
            <p>
                You can alternatively <a href={`/admin/translation/${this.props.gloss.id}/edit`}>edit the gloss on the dashboard</a>.
            </p>
        </div>;
    }
}

export default EDEditComponentFactory;
