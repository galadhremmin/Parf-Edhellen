import React from 'react';
import EDBookGloss from './book-gloss';

/**
 * Represents a single section of the book. A section is usually dedicated to a language.
 */
class EDBookSection extends React.Component {
    onReferenceLinkClick(ev) {
        if (this.props.onReferenceLinkClick) {
            this.props.onReferenceLinkClick(ev);
        }
    }

    render() {
        const className = `col-sm-${this.props.columnsMax} col-md-${this.props.columnsMid} col-lg-${this.props.columnsMin}`;
        const language = this.props.section.language;

        return <article className={className}>
            <header>
                <h2 rel="language-box">
                    { language.is_unusual ? '† ' : '' }
                    { language.name }
                    &nbsp;
                    <span className="tengwar">{ language.tengwar }</span>
                </h2>
            </header>
            <section className="language-box" id={`language-box-${ language.id }`}>
                {this.props.section.glosses.map(
                    g => <EDBookGloss gloss={g}
                                      language={language}
                                      key={g.id}
                                      onReferenceLinkClick={this.onReferenceLinkClick.bind(this)} />
                )}
            </section>
        </article>;
    }
}

export default EDBookSection;
