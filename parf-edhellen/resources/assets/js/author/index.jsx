import React from 'react';
import ReactDOM from 'react-dom';

class MarkdownEditor extends React.Component, OnInit  {
  
  componentWillMount() {
    console.log('Hello world!');
  }

  render() {
    return (
      <div>
        <ul class="nav nav-tabs">
          <li role="presentation" class="active"><a href="#">Edit</a></li>
          <li role="presentation"><a href="#">Preview</a></li>
        </ul>
        <textarea class="form-control" id="ed-author-profile" name="profile" rows="15"></textarea>
      </div>
    );
  }
}

