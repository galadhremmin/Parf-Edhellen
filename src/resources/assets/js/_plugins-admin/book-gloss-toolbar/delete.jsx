import React from 'react';
import { EDDialog, EDComponentFactory } from 'ed-components/dialog';
import EDDeleteComponentFactory from './factories/delete';

class EDDeleteGlossPlugin extends React.Component {
    constructor(props, context) {
        super(props, context);

        this.state = {
            dialogOpen: false,
            componentFactory: this.createComponentFactory()
        };
    }

    createComponentFactory() {
        const factory = new EDDeleteComponentFactory(this.props.gloss);
        factory.onDone = this.onDeleteSuccess.bind(this);
        factory.onFailed = this.onDeleteFailed.bind(this);

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
    
    onDeleteSuccess(gloss) {
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

    onDeleteFailed(gloss) {
        this.onCloseDialog();
    }

    render() {
        const gloss = this.props.gloss;
        return <span>
            <a href="#" className="ed-admin-tool" onClick={this.onOpenDialog.bind(this)} title="Delete gloss">
                <span className="glyphicon glyphicon-trash" />
            </a>
            <EDDialog modalProps={{ bsSize: 'small', show: this.state.dialogOpen, onHide: this.onCloseDialog.bind(this) }}
                      componentProps={{ gloss }}
                      componentFactory={this.state.componentFactory} />
        </span>;
    }
}

export default EDDeleteGlossPlugin;
