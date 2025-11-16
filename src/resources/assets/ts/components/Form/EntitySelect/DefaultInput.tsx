import classNames from 'classnames';
import type { RenderInputComponentProps } from 'react-autosuggest';

function DefaultInput(props: RenderInputComponentProps) {
    const { key, ...inputProps } = props;
    const className = classNames('form-control', inputProps.className);
    return <input key={key} {...inputProps} className={className} />;
}

export default DefaultInput;
