import React from 'react';
import classNames from 'classnames';
import { connect } from 'react-redux';
import EDMarkdownEditor from '../../_shared/components/markdown-editor';

class EDSentenceForm extends React.Component {

    onSubmit() {
        
    }
 
    render() {
        return <form onSubmit={this.onSubmit.bind(this)}>
            <div className="form-group">
                <label htmlFor="ed-sentence-name" className="control-label">Name</label>
                <input type="text" className="form-control" id="ed-sentence-name" name="name" />
            </div>
            <div className="form-group">
                <label htmlFor="ed-sentence-source" className="control-label">Source</label>
                <input type="text" className="form-control" id="ed-sentence-source" name="source" />
            </div>
            <div className="form-group">
                <label htmlFor="ed-sentence-language" className="control-label">Language</label>
                <select className="form-control" id="ed-sentence-language">
                    {this.props.languages
                        .filter(l => l.is_invented)
                        .map(l => <option value={l.id} key={l.id}>{l.name}</option>)}
                </select>
            </div>
            <div className="form-group">
                <label htmlFor="ed-sentence-description" className="control-label">Summary</label>
                <textarea id="ed-sentence-description" className="form-control" name="description"></textarea>
            </div>
            <div className="form-group">
                <label htmlFor="ed-sentence-long-description" className="control-label">Description</label>
                <EDMarkdownEditor componentId="ed-sentence-long-description" componentName="long-description" />
            </div>
        </form>;
    }
}

const mapStateToProps = state => {
    return {
        languages: state.languages
    };
};

export default connect(mapStateToProps)(EDSentenceForm);
