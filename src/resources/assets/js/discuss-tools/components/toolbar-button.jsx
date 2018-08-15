import React from 'react';
import EDAPI from 'ed-api';
import GlyphButton from 'elfdict/components/glyph-button';

export default class ToolbarButton extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            active: false
        };

        this.onActiveClick = this.onActiveClick.bind(this);
        this.onActiveChange = this.onActiveChange.bind(this);
    }

    componentWillMount() {
        this.loadApiState();
    }

    shouldComponentUpdate(nextProps, nextState) {
        return nextState.active !== this.state.active;
    }

    loadApiState() {
        const { apiPath } = this.props;
        let promise = null;

        if (apiPath) {
            promise = EDAPI.get(apiPath)
                .then(resp => !! resp.data[this.props.apiProp]);
        } else {
            primise = Promise.resolve(false);
        }

        promise.then(this.onActiveChange);
    }

    onActiveClick(ev) {
        ev.preventDefault();

        const { apiPath } = this.props;
        let promise = null;
        if (this.state.active) {
            promise = EDAPI.delete(apiPath);
        } else {
            promise = EDAPI.post(apiPath);
        }

        promise.then(resp => !! resp.data[this.props.apiProp])
            .then(this.onActiveChange);
    }

    onActiveChange(active) {
        if (this.state.active !== active) {
            this.setState({ active });
        }
    }

    render() {
        const props = { 
            bsStyle: "default", 
            bsSize: "small", 
            onClick: this.onActiveClick
        };
        const action = this.state.active 
            ? this.props.activeLabel : this.props.inactiveLabel;

        return <GlyphButton buttonProps={props} glyph={this.props.glyph}>
            {action}
        </GlyphButton>;
    }
}
