import React from 'react';
import {
    ResponsiveContainer,
    BarChart,
    CartesianGrid,
    XAxis,
    YAxis,
    Tooltip,
    Legend,
    Bar
} from 'recharts';
import EDAPI from 'ed-api';
import classNames from 'classnames';

const ChartColors = ["#00818a", "#404b69", "#283149", "#6c5b7c", "#c06c84", "#f67280", "#f8b595"];

class EDSystemErrorList extends React.Component {
    constructor(props, context) {
        super(props, context);
        console.log(props);

        this.state = {
            ...(this.buildState(props.value))
        }
    }

    componentWillReceiveProps(props) {
        if (props.value) {
            this.setValue(props.value);
        }
    }

    setValue(value) {
        this.setState(this.buildState(value));
    }

    getInitialState() {
        return {
            errors: [],
            openErrorId: 0,
            exceptions: [],
            selectedExceptions: [],
            hasNavigation: false,
            nextPageUrl: null,
            previousPageUrl: null,
            errorCategories: []
        };
    }

    buildState(value) {
        if (! value) {
            return this.getInitialState();
        }

        // Build an array of exceptions and associate each error with a unique exception type.
        const exceptions = {};
        const errors = value.errors.data;

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

        // Build chart dataset for visualizing errors per week 
        const errorCategories = value.errorsByWeek.reduce((arr, v) => {
            if (arr.indexOf(v.category) === -1) {
                arr.push(v.category);
            }
            return arr;
        }, []);
        const errorsByWeek = value.errorsByWeek.reduce((arr, set) => {

            const x = `${set.year} - ${set.week}`;
            let w = arr.find(p => p.x === x);
            if (! w) {
                w = { x };
                arr.push(w);
            }

            w[set.category] = set.number_of_errors;
            return arr;
        }, []);
        
        return {
            errors,
            openErrorId: 0,
            exceptions: exceptionStrings,
            selectedExceptions: exceptionStrings.filter(
                ex => ex !== 'Illuminate\\Auth\\AuthenticationException'   &&
                      ex !== 'Illuminate\\Session\\TokenMismatchException' &&
                      ex !== 'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException'),
            hasNavigation: value.errors.next_page_url || value.errors.prev_page_url,
            nextPageUrl: value.errors.next_page_url,
            previousPageUrl: value.errors.prev_page_url,
            errorsByWeek,
            errorCategories
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

    onNavigation(url, ev) {
        ev.preventDefault();

        EDAPI.get(url).then(resp => {
            this.setValue(resp.data);
        });
    }

    render() {
        return <div>
            {this.state.errorsByWeek ? <ResponsiveContainer width="100%" aspect={4/1.5}>
                <BarChart width={730} height={250} data={this.state.errorsByWeek}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="x" />
                    <YAxis />
                    <Tooltip />
                    <Legend />
                    {this.state.errorCategories.map((category, i) => <Bar key={category} dataKey={category} fill={ChartColors[i % ChartColors.length]} stackId="x" />)}
                </BarChart>
            </ResponsiveContainer> : null}
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
                            <th>Category</th>
                            <th>Exception</th>
                        </tr>
                    </thead>
                    {this.state.errors.filter(this.filterErrors.bind(this))
                        .map((error, i) => <tbody key={error.id}>
                        <tr onClick={this.onOpenError.bind(this, error.id)}>
                            <td>{error.created_at}</td>
                            <td>{error.category}</td>
                            <td>{error.message}</td>
                        </tr>
                        {this.state.openErrorId === error.id ?
                        <tr>
                            <td colSpan="3">
                                <p>
                                    <strong>URL</strong>: {error.url}<br />
                                    <strong>User</strong>: {error.account_id} ({error.ip})
                                </p>
                                {error.error ?
                                <code style={{ whiteSpace: 'pre-wrap' }}>
                                    {error.error}
                                </code> : null}
                            </td>
                        </tr> : null}
                    </tbody>)}
                </table>
            </div>
            {this.state.hasNavigation ?
                <nav>
                    <ul className="pager">
                        <li className={classNames('previous', { 'disabled': !this.state.previousPageUrl })}><a href={this.state.previousPageUrl} onClick={this.onNavigation.bind(this, this.state.previousPageUrl)}><span aria-hidden="true">&larr;</span> Older</a></li>
                        <li className={classNames('next', { 'disabled': !this.state.nextPageUrl })}><a href={this.state.nextPageUrl} onClick={this.onNavigation.bind(this, this.state.nextPageUrl)}>Newer <span aria-hidden="true">&rarr;</span></a></li>
                    </ul>
                </nav> : null}
        </div>;
    }
}

EDSystemErrorList.defaultProps = {
    errors: { data: [] }
};

export default EDSystemErrorList;
