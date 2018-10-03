import React from 'react';
import EDBookGloss from './book-gloss';
import EDConfig from 'ed-config';

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
        const language = this.props.section.language;
        const sectionPlugins = EDConfig.pluginsFor('book-gloss-section');

        return <article className="ed-glossary__language">
            <header>
                <h2>
                    { language.is_unusual ? '† ' : '' }
                    { language.name }
                    &nbsp;
                    <span className="tengwar">{ language.tengwar }</span>
                </h2>
            </header>
            <section className="ed-glossary__language__words" id={`language-box-${ language.id }`}>
                {this.props.section.glosses.map(
                    g => <EDBookGloss gloss={g}
                                      language={language}
                                      key={g.id}
                                      onReferenceLinkClick={this.onReferenceLinkClick.bind(this)} />
                )}
            </section>
            {sectionPlugins.length > 0 && <section>
                {sectionPlugins.map((PluginComponent, i) => <PluginComponent key={i} 
                    hostComponent={this} language={language} glosses={this.props.section.glosses} />)}
            </section>}
        </article>;
    }
}

export default EDBookSection;
