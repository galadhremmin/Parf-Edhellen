import TextIcon from '@root/components/TextIcon';
import { IProps } from './GlossInflections._types';

const GlossInflections = (props: IProps) => {
    const { gloss } = props;

    if (!gloss.inflections) {
        return null;
    }
    const inflectionGroups = Object.keys(gloss.inflections);

    return <section className="GlossDetails details">
        <header>
            <h4>Inflections</h4>
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
                    {inflectionGroups.map((inflectionGroup: string) => {
                        const inflections = gloss.inflections[inflectionGroup];
                        const firstInflection = inflections[0];

                        return <tr key={firstInflection.inflectionGroupUuid}>
                            <td>
                                {firstInflection.isNeologism ? <TextIcon icon="asterisk" /> : null}
                                {firstInflection.isRejected ? <s>{firstInflection.word}</s> : firstInflection.word}
                            </td>
                            <td>
                                <em>{firstInflection.speech?.name}</em>
                                {' ' + inflections.filter(i => !! i.inflection).map(i => i.inflection.name).join(' ')}
                            </td>
                            <td>
                                {firstInflection.sentenceUrl ? <a href={firstInflection.sentenceUrl} title={`Go to ${firstInflection.sentence.name}.`}>
                                    {firstInflection.sentence.name}
                                </a> : (firstInflection.source ? `✧ ${firstInflection.source}` : '')}
                            </td>
                        </tr>;
                    })}
                </tbody>
            </table>
        </div>
    </section>;
};

export default GlossInflections;
