import React from 'react';
import classNames from 'classnames';
import EDConfig from 'ed-config';

class EDSystemErrorList extends React.Component {
    constructor(props, context) {
        super(props, context);

        this.state = {
            ...(this.makeErrorState(props.errors))
        }
    }

    componentWillReceiveProps(props) {
        if (Array.isArray(props.errors)) {
            this.setState(this.makeErrorState(props.errors));
        }
    }

    makeErrorState(errors) {
        const exceptions = {};

        for (let error of errors) {
            let pos = error.message.indexOf(':');
            let exception = pos === -1 ? error.message : error.message.substr(0, pos);
            if (exceptions[exception] === undefined) {
                exceptions[exception] = true;
            }

            error._exception = exception;
        }

        const exceptionStrings = Object.keys(exceptions);
        exceptionStrings.sort();
        
        return {
            errors,
            openErrorId: 0,
            exceptions: exceptionStrings,
            selectedExceptions: exceptionStrings.filter(
                ex => ex !== 'Illuminate\\Auth\\AuthenticationException')
        };
    }

    filterErrors(error) {
        return this.state.selectedExceptions.indexOf(error._exception) > -1;
    }

    onOpenError(id) {
        this.setState({
            openErrorId: id
        });
    }

    onExceptionChange(ev) {
        const exceptions = [];
        
        for (let o of ev.target.options) {
            if (o.selected) {
                exceptions.push(o.value);
            }
        }

        this.setState({
            selectedExceptions: exceptions
        });
    }

    render() {
        return <div>
            <select className="form-control" multiple={true} value={this.state.selectedExceptions}
                onChange={this.onExceptionChange.bind(this)}>
                {this.state.exceptions.map((exception, i) => 
                    <option key={i} value={exception}>{exception}</option>)}
            </select>
            <div className="table-responsive">
                <table className="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Exception</th>
                        </tr>
                    </thead>
                    {this.props.errors.filter(this.filterErrors.bind(this))
                        .map((error, i) => <tbody key={error.id}>
                        <tr onClick={this.onOpenError.bind(this, error.id)}>
                            <td>{error.created_at}</td>
                            <td>{error.message}</td>
                        </tr>
                        {this.state.openErrorId === error.id ?
                        <tr>
                            <td colSpan="2">
                                <p>
                                    <strong>URL</strong>: {error.url}<br />
                                    <strong>User</strong>: {error.account_id} ({error.ip})
                                </p>
                                {error.error ?
                                <code style={{ whiteSpace: 'pre-wrap' }}>
                                    {error.error}
                                </code> : undefined}
                            </td>
                        </tr> : undefined}
                    </tbody>)}
                </table>
            </div>
        </div>;
    }
}

EDSystemErrorList.defaultProps = {
    errors: []
};

export default EDSystemErrorList;
