import React from 'react';

import Gloss from './Gloss';
import { IProps } from './Language._types';

export default class GlossaryLanguage extends React.PureComponent<IProps> {
    public render() {
        const { glosses, language, onReferenceLinkClick } = this.props;

        return <article className="ed-glossary__language">
            <header>
                <h2>
                    { language.isUnusual ? '† ' : '' }
                    { language.name }
                    &nbsp;
                    <span className="tengwar">{ language.tengwar }</span>
                </h2>
            </header>
            <section className="ed-glossary__language__words">
                {glosses.map((gloss) => <Gloss gloss={gloss} key={gloss.id}
                    onReferenceLinkClick={onReferenceLinkClick} />)}
            </section>
            {this._renderPlugins()}
        </article>;
    }

    private _renderPlugins() {
        /*
            {sectionPlugins.length > 0 && <section>
            {sectionPlugins.map((PluginComponent, i) => <PluginComponent key={i}
                hostComponent={this} language={language} glosses={this.props.section.glosses} />)}
        </section>}
        */

        return null as React.ReactNode;
    }
}