import React from 'react';
import ReactDOM from 'react-dom';
import axios from 'axios';

class EDSearchItem extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    return <li>
      <a href={'/w/' + encodeURIComponent(this.props.item.nk)}>
        {this.props.item.k}
      </a>
    </li>;
  }
}

class EDSearchTool extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      result: [],
      term: null
    };
  }

  componentDidMount() {
    const searchElement = document.getElementById('search-query-field');
    searchElement.addEventListener('keyup', this.searchChange.bind(this));
  }

  searchChange(ev) {
    if (ev.which === 13) {
      ev.preventDefault();
      this.search(ev.target.value);
    }
  }

  search(term) {
    axios.post('/api/v1/book/find', { term: term }).then(resp => {
      this.setState({
        result: resp.data
      });
    })

    this.setState({
      term: term,
      searchDelay: 0
    });
  }

  render() {
    return <ul>
      {this.state.result.map((r, i) => <EDSearchItem key={i + 1} item={r} />)}
    </ul>;
  }
};

ReactDOM.render(
  <EDSearchTool />,
  document.getElementById('search-result-navigator')
);