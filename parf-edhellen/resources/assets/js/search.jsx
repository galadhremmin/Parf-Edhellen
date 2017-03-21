import React from 'react';
import ReactDOM from 'react-dom';
import axios from 'axios';

class EDSearchItem extends React.Component {
  constructor(props) {
    super(props);
  }

  navigate(ev) {
    ev.preventDefault();

    const title = `${this.props.k} - Parf Edhellen`;
    window.history.pushState(null, title, address);

    const address = ev.target.href;
    axios.get(address, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest' // this is important for the controller!
      }
    }).then(this.loadPage.bind(this));
  }

  loadPage(resp) {
    document.getElementById('result').innerHTML = resp.data; // XSS vulnerability
  }

  render() {
    return <li>
      <a href={'/w/' + encodeURIComponent(this.props.item.nk)} onClick={this.navigate.bind(this)}>
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
        result: resp.data,
        term: term,
        searchDelay: 0
      });
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