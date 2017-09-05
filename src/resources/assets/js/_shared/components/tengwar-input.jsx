import React from 'react';
import { transcribe } from '../tengwar';
import { Modal } from 'react-bootstrap';

class EDTengwarInput extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            ...(this.createStateForValue(props.value)),
            mode: props.tengwarMode || undefined,
            subject: props.transcriptionSubject || undefined,
            showError: false
        };
    }

    componentWillReceiveProps(props) {
        if (props.value) {
            this.setValue(props.value);
        }

        let state = {};
        let change = false;

        if (props.tengwarMode !== undefined) {
            state.mode = props.tengwarMode;
            change = true;
        }

        if (props.transcriptionSubject !== undefined) {
            state.subject = props.transcriptionSubject;
            change = true;
        }

        if (change) {
            this.setState(state);
        }
    }

    createStateForValue(value) {
        if (! value) {
            value = '';
        }

        return {
            value
        };
    }

    /**
     * Sets the tengwar mode.
     * @param {*} mode 
     */
    setTengwarMode(mode) {
        this.setState({
            mode
        });
    }

    /**
     * Sets the word that should be transcribed upon pressing the button 'Transcribe'
     * @param {*} subject 
     */
    setSubject(subject) {
        this.setState({
            subject
        });
    }

    /**
     * Updates the input component's current value.
     * @param {Object} value 
     */
    setValue(value) {
        const originalValue = this.state.value;
        this.setState(this.createStateForValue(value));

        if (originalValue !== value) {
            this.triggerChange();
        }
    }

    /**
     * Gets the component's current value.
     */
    getValue() {
        return this.state.value;
    }

    /**
     * Gets current visual value.
     */
    getText() {
        return this.getValue();
    }

    /**
     * Gives focus to the component's input element.
     */
    focus() {
        this.tengwarInput.focus();
    }

    triggerChange() {
        if (typeof this.props.onChange === 'function') {
            window.setTimeout(() => {
                this.props.onChange({ 
                    target: this,
                    value: this.getValue()
                });
            }, 0);
        }
    }

    onTengwarChange(ev) {
        this.setValue(ev.target.value);
    }

    onTranscribeClick(ev) {
        ev.preventDefault();

        const mode = this.state.mode;
        const subject = this.state.subject;
        if (! mode || ! subject) {
            this.setState({showError: true});
            return;
        }

        const transcription = transcribe(subject, mode, false);
        this.setValue(transcription);
    }

    onModalClose() {
        this.setState({showError: false});
    }

    render() {
        return <div>
            <div className="input-group">
                <input id={this.props.componentId} className="form-control tengwar" type="text" ref={input => this.tengwarInput = input} 
                    value={this.state.value} onChange={this.onTengwarChange.bind(this)} />
                <div className="input-group-addon">
                    <a href="#" onClick={this.onTranscribeClick.bind(this)}>Transcribe</a>
                </div>
            </div>
            <Modal bsSize="small" show={this.state.showError} onHide={this.onModalClose.bind(this)}>
                <Modal.Header>
                    <Modal.Title>Cannot transcribe &ldquo;{this.state.subject}&rdquo;</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    <p>You must select a language that the transcription service supports.</p>
                    <p>If the language you have selected is not supported by the transcriber, you will have to perform the transcription manually.</p>
                </Modal.Body>
                <Modal.Footer>
                  <button onClick={this.onModalClose.bind(this)} className="btn btn-primary">OK</button>
                </Modal.Footer>
            </Modal>
        </div>;
    }
}

EDTengwarInput.defaultProps = {
    componentName: 'tengwar',
    componentId: undefined,
    tengwarMode: undefined,
    transcriptionSubject: undefined,
    value: ''
};

export default EDTengwarInput;
