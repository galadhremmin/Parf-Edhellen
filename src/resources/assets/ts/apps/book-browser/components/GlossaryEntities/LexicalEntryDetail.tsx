import classNames from 'classnames';
import HtmlInject from '@root/components/HtmlInject';
import type { IProps } from './LexicalEntryDetail._types';

function LexicalEntryDetail(props: IProps) {
    const {
        detail: d,
        onReferenceLinkClick,
    } = props;

    return <section className="GlossDetails details">
        <header>
            <h4>{d.category}</h4>
        </header>
        <div className={classNames('details__body', { [String(d.type)]: !!d.type })}>
            <HtmlInject html={d.text} onReferenceLinkClick={onReferenceLinkClick} />
        </div>
    </section>;
}

export default LexicalEntryDetail;
