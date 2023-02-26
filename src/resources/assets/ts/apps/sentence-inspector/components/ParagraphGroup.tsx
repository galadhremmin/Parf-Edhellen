import { IProps } from './ParagraphGroup._types';

const ParagraphGroup = (props: IProps) => {
    const className = ['p-group'];
    if (props.selected) {
        className.push('selected');
    }

    return <div className={className.join(' ')}>
        {props.children}
    </div>;
};

export default ParagraphGroup;
