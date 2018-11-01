import React from 'react';
import { connect } from 'react-redux';

import Spinner from '../../../components/Spinner';
import { ILanguageEntity } from '../../../connectors/backend/BookApiConnector._types';
import { IRootReducer } from '../reducers';
import { IProps } from './Glossary._types';

export class Glossary extends React.PureComponent<IProps> {
    public render() {
        if (this.props.loading) {
            return <Spinner />;
        }

        if (this.props.isEmpty) {
            this._renderEmptyDictionary();
        }

        return this._renderDictionary();
    }

    private _renderEmptyDictionary() {
        return <div>
            <h3>Alas! What you are looking for does not exist!</h3>
            <p>The word <em>{this.props.word}</em> does not exist in the dictionary.</p>
        </div>;
    }

    private _renderDictionary() {
        return <React.Fragment>
            {this._renderCommonLanguages()}
            {this._renderUnusualLanguages()}
        </React.Fragment>;
    }

    private _renderCommonLanguages() {
        if (this.props.languages.length === 0) {
            return null;
        }

        return this._renderLanguages(this.props.languages);
    }

    private _renderUnusualLanguages() {
        if (this.props.unusualLanguages.length === 0) {
            return null;
        }

        const abstract = <p>
            <strong>Beware, older languages below!</strong> {' '}
            The languages below were invented during Tolkien's earlier period and should be used with caution. {' '}
            Remember to never, ever mix words from different languages!
        </p>;
        return this._renderLanguages(this.props.unusualLanguages, abstract, ['ed-glossary--unusual']);
    }

    private _renderLanguages(languages: ILanguageEntity[], abstract: React.ReactNode = null, //
        classNames: string[] = []) {
        classNames = [ 'ed-glossary', ...classNames ];
        if (this.props.single) {
            classNames.push('ed-glossary--single');
        }

        return <section className={classNames.join(' ')}>
            {abstract}
            {languages.map((language) => language.name)}
        </section>;
    }
}

const mapStateToProps = (state: IRootReducer) => ({
    ...state.glossary,

    glosses: state.glosses,
    isEmpty: state.languages.isEmpty,
    languages: state.languages.common,
    unusualLanguages: state.languages.unusual,
});

export default connect(mapStateToProps)(Glossary);
