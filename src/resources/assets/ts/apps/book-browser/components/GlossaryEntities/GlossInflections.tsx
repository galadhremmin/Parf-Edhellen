import React from 'react';

import { IProps } from './GlossInflections._types';

const GlossInflections = (props: IProps) => {
    const { gloss } = props;

    if (!gloss.inflections) {
        return null;
    }

    const sentenceIds = Object.keys(gloss.inflections);

    return <section className="GlossDetails details">
        <header>
            <h4>Inflections (from phrases)</h4>
        </header>
        <div className="table-responsive details__body">
            <table className="table table-striped table-hover table-condensed">
                <thead>
                    <tr>
                        <th>Word</th>
                        <th>Inflection</th>
                        <th>Source</th>
                    </tr>
                </thead>
                <tbody>
                    {sentenceIds.map((sentenceId) => {
                        const inflections = gloss.inflections[sentenceId];
                        const firstInflection = inflections[0];

                        return <tr key={sentenceId}>
                            <td>{firstInflection.word}</td>
                            <td>
                                <em>{firstInflection.speech}</em>
                                {inflections.filter((inf) => !! inf.inflection).map(
                                    (inf, i) => <span key={`${sentenceId}-${i}`}>
                                        {' '}{inf.inflection}
                                    </span>,
                                )}
                            </td>
                            <td>
                                <a href={firstInflection.sentenceUrl} title={`Go to ${firstInflection.sentenceName}.`}>
                                    {firstInflection.sentenceName}
                                </a>
                            </td>
                        </tr>;
                    })}
                </tbody>
            </table>
        </div>
    </section>;
};

export default GlossInflections;
