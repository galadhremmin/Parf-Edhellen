import React from 'react';
import axios from 'axios';
import EDConfig from 'ed-config';
import ComponentFactory from './component-factory';
import EDTranslationSelect from 'ed-components/translation-select';

class DeleteComponentFactory extends ComponentFactory {
    get titleComponent() {
        return props => <span>
            <span className="glyphicon glyphicon-trash" />
            {' '}
            Delete gloss &ldquo;{props.gloss.word}&rdquo;
        </span>;
    }

    get bodyComponent() {
        return props => <BodyComponent {...props} onSubmit={this.onSubmit.bind(this, props.gloss)} onChange={this.onChange.bind(this)} />;
    }

    get footerComponent() {
        return props => <div>
            <button className="btn btn-default" onClick={this.onSubmit.bind(this, props.gloss)}>Delete</button>
            <button className="btn btn-primary" onClick={this.done.bind(this)}>Cancel</button>
        </div>;
    }

    onChange(gloss) {
        this.replacementGloss = gloss;
    }

    onSubmit(gloss) {
        axios.delete(`/admin/translation/${gloss.id}` + (this.replacementGloss ? `?replacement_id=${this.replacementGloss.id}` : ''))
            .then(this.onDeleted.bind(this))
    }

    onDeleted(resp) {
        
    }
}

class BodyComponent extends React.Component {
    constructor(props, context) {
        super(props, context);

        this.state = {
            error: false, 
            gloss: undefined
        };
    }
    
    onReplacementSelect(data) {
        if (data.value !== undefined && data.value.id === this.props.gloss.id) {
            this.setState({
                error: true
            });

            return;
        }        
    
        this.setState({
            error: false,
            gloss: data.value
        });

        window.setTimeout(() => this.props.onChange.call(this, data.value), 0);
    }

    render() {
        return <form onSubmit={this.props.onSubmit.bind(this)}>
            {this.state.error ? <div className="alert alert-danger">
                <p>You cannot select the same gloss as the one you wish to delete.</p>    
            </div> : undefined}
            <p>
                It is recommended to provide an alternative gloss for <em>{this.props.gloss.word}</em>{' '}
                in order to ensure that there are no dangling references, such as phrases with missing
                words as a result of this deletion.
            </p>
            <p>
                Remember! A gloss is not <em>permanently</em>{' '}deleted. An system administrator can restore it.
            </p>
            <div className="form-group">
                <label htmlFor="ed-deletion-replacement">Replacement translation:</label>
                <EDTranslationSelect componentId="ed-deletion-replacement" componentName="replacement_translation_id"
                    languageId={this.props.gloss.language_id} onChange={this.onReplacementSelect.bind(this)} value={this.state.gloss} />
            </div>
        </form>;
    }
}

export default DeleteComponentFactory;
