import React from 'react';
import EDBookGloss from './book-gloss';

/**
 * Represents a single section of the book. A section is usually dedicated to a language.
 */
class EDBookSection extends React.Component {
    render() {
        const className = `col-sm-${this.props.columnsMax} col-md-${this.props.columnsMid} col-lg-${this.props.columnsMin}`;
        const language = this.props.section.language;

        return <article className={className}>
            <header>
                <h2 rel="language-box">
                    { language.Name }
                    &nbsp;
                    <span className="tengwar">{ language.Tengwar }</span>
                </h2>
            </header>
            <section className="language-box" id={`language-box-${ language.ID }`}>
                {this.props.section.glosses.map(
                    g => <EDBookGloss gloss={g} language={language} key={g.TranslationID} />
                )}
            </section>
        </article>;
    }
}

export default EDBookSection;
