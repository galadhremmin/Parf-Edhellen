import { useLanguageLookup } from './LanguageLookupContext';
import type { IProps } from './RootForm._types';

import './RootForm.scss';

/**
 * Renders a root-speech ancestor the way Eldamo typesets roots: "√" + UPPERCASE form, with the
 * language's own `mark` (e.g. "M" for Middle Primitive Elvish) as a superscript prefix — Eldamo
 * writes this as "ᴹ√GALAD". `mark` is configured per language (see the `languages.mark` column;
 * null by default), so no period/id list needs hardcoding here. Roots are cited in their own
 * uppercase spelling (`parentForm`) — unlike ordinary ancestors, they should never fall back to
 * a resolved entry's plain-cased `Word` record (e.g. "gal"), since that loses the root
 * convention entirely.
 */
const RootForm = (props: IProps) => {
    const { form, languageId, url } = props;
    const { getLanguage } = useLanguageLookup();
    const mark = languageId ? getLanguage(languageId)?.mark : null;

    const content = <>
        {mark && <sup className="RootForm--mark">{mark}</sup>}
        <span className="RootForm--radical">√</span>
        {form.toLocaleUpperCase()}
    </>;

    return url
        ? <a className="RootForm" href={url}>{content}</a>
        : <span className="RootForm">{content}</span>;
};

export default RootForm;
