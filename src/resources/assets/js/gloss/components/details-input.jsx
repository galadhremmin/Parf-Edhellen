import React from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import EDMarkdownEditor from 'ed-components/markdown-editor';
import { EDStatefulFormComponent } from 'ed-form';

class EDDetailsInput extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            ...(this.createInitialState(props.abstract, props.details))
        };
    }

    componentWillReceiveProps(props) {
        if ((props.abstract && props.abstract !== this.state.abstract) || 
            (props.details && JSON.stringify(props.details) !== JSON.stringify(this.state.details))) {
            this.setState(this.createInitialState(props.abstract || this.props.abstract, 
                props.details || this.props.details));
        }
    }

    /**
     * Converts the specified _value_ to a value compatible with local component state.
     * @param {Object} value
     */
    createInitialState(abstract, details) {
        let value = abstract;

        if (! value) {
            value = '';
        } else {
            value = abstract = abstract.trim();
        }

        if (Array.isArray(details)) {
            details.sort((a, b) => a.order < b.order ? -1 : (a.order === b.order ? 0 : 1));

            for (var detail of details) {
                value += `\n\n## ${detail.category}\n${detail.text}`;
            }

            value = value.trim();
        }

        return { 
            value,
            abstract,
            details
        };
    }

    /**
     * Gets an array containing the inflections currently selected.
     */
    getAbstract() {
        return this.state.abstract;
    }

    /**
     * Gets current visual value.
     */
    getDetails() {
        return this.state.details;
    }

    /**
     * Gives focus to the component's input element.
     */
    focus() {
        this.markdownEditor.focus();
    }

    onChange(ev) {
        const value = ev.target.getValue();
        const divider = '## ';
        const newLine = "\n";
        const parts = value.split(divider);

        let abstract = parts[0].trim();
        let details = parts.length > 1 
            ? parts.slice(1).map((p, i) => {
                const lines = p.split(newLine);
                return {
                    category: lines[0],
                    text: lines.length > 1 
                        ? lines.splice(1).join(newLine)
                        : '',
                    order: (i + 10) * 10
                };
            }) : [];
        
        this.setState({
            value,
            abstract, 
            details
        });

        this.triggerChange();
    }

    triggerChange() {
        if (typeof this.props.onChange === 'function') {
            window.setTimeout(() => {
                this.props.onChange({ 
                    target: this,
                    abstract: this.state.abstract,
                    details: this.state.details
                });
            }, 0);
        }
    }

    render() {
        return <div>
            <EDMarkdownEditor ref={e => this.markdownEditor = e} componentId={this.props.componentId} 
                componentName={this.props.componentName} rows={8} value={this.state.value} 
                onChange={this.onChange.bind(this)} />
        </div>;
    }
};

EDDetailsInput.defaultProps = {
    componentName: 'details',
    componentId: undefined,
    value: [],
    required: false
};

export default EDDetailsInput;
