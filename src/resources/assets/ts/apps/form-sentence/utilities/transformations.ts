import convert from '@root/apps/sentence-inspector/utilities/TextConverter';
import {
    ISentenceFragmentEntity,
    ITextTransformation,
    ITextTransformationsMap,
} from '@root/connectors/backend/IBookApi';

/**
 * Converts the specified transformations object to strings given fragments.
 *
 * @param transformations transformations object
 * @param fragments sentence fragments
 */
export const convertTransformationsToStrings = (transformations: ITextTransformationsMap, fragments: ISentenceFragmentEntity[]) => {
    const defaultTexts = {
        latin: '',
    };

    if (! transformations || ! Array.isArray(fragments)) {
        return defaultTexts;
    }

    return Object.keys(transformations).reduce((texts, transformerName) => {
        return {
            ...texts,
            [transformerName]: convertTransformationToString(transformations[transformerName], fragments),
        };
    }, defaultTexts);
};

export const convertTransformationToString = (transformation: ITextTransformation, fragments: ISentenceFragmentEntity[]) => {
    if (! transformation || ! Array.isArray(fragments)) {
        return null;
    }

    const text = convert(null, transformation, fragments);
    return text.paragraphs.reduce((paragraphs, paragraph) => {
        return [
            ...paragraphs,
            paragraph.map((f) => f.fragment).join(''),
        ];
    }, []).join('\n');
};

