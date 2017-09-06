import React from 'react';
import { EDDialog, EDComponentFactory } from 'ed-components/dialog';
import EDEditComponentFactory from './factories/edit';

class EDEditGlossPlugin extends React.Component {
    constructor(props, context) {
        super(props, context);

        this.state = {
            dialogOpen: false,
            componentFactory: this.createComponentFactory()
        };
    }

    createComponentFactory() {
        const factory = new EDEditComponentFactory(this.props.gloss);
        factory.onDone = this.onEditSuccess.bind(this);
        factory.onFailed = this.onEditFailed.bind(this);

        return factory;
    }

    onOpenDialog(ev) {
        ev.preventDefault();

        this.setState({
            dialogOpen: true
        });
    }
    
    onCloseDialog() {
        this.setState({
            dialogOpen: false
        });
    }
    
    onEditSuccess(gloss) {
        if (gloss.id !== this.props.gloss.id) {
            return;
        }
        
        // Begin closing the dialogue ...
        this.onCloseDialog();

        // ... but wait for the animation to finish before unmounting the host component.
        window.setTimeout(() => {
            this.props.hostComponent.setState({
                isDeleted: true
            });
        }, 800);
    }

    onEditFailed(gloss) {
        this.onCloseDialog();
    }

    render() {
        const gloss = this.props.gloss;
        return <span>
            <a href="#" onClick={this.onOpenDialog.bind(this)} className="ed-admin-tool" title="Edit gloss">
                <span className="glyphicon glyphicon-edit" />
            </a>
            <EDDialog modalProps={{ bsSize: 'small', show: this.state.dialogOpen, onHide: this.onCloseDialog.bind(this) }}
                      componentProps={{ gloss }}
                      componentFactory={this.state.componentFactory} />
        </span>;
    }
}

export default EDEditGlossPlugin;
