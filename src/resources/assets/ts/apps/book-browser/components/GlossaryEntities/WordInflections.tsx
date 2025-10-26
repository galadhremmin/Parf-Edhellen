import TextIcon from '@root/components/TextIcon';
import UngweInflectionsDialog, { isEligibleForUngweInflections } from './UngweInflectionsDialog';
import { IProps } from './WordInflections._types';
import React, { useCallback, useState } from 'react';

const WordInflections = (props: IProps) => {
    const { lexicalEntry: entry } = props;
    const [dialogOpen, setDialogOpen] = useState(false);

    const _onUngweInflectClick = useCallback((ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        setDialogOpen(true);
    }, []);

    const _onDialogDismiss = useCallback(() => {
        setDialogOpen(false);
    }, []);

    const isUngweEligible = isEligibleForUngweInflections(entry);
    const visible = !! entry?.inflections || isUngweEligible;

    if (! visible) {
        return null;
    }

    const inflectionGroups = Object.keys(entry.inflections || {});

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
                        const inflections = entry.inflections[inflectionGroup];
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
                {isUngweEligible && <tfoot>
                    <tr>
                        <td colSpan={3} className="text-center">
                            <a href="#" onClick={_onUngweInflectClick}>
                                Inflect with Quettali
                            </a>
                        </td>
                    </tr>
                </tfoot>}
            </table>
        </div>
        <UngweInflectionsDialog
            lexicalEntryId={entry.id}
            open={dialogOpen}
            onDismiss={_onDialogDismiss}
        />
    </section>;
};

export default WordInflections;
