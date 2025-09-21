import classNames from 'classnames';
import { RenderInputComponentProps } from 'react-autosuggest';

function DefaultInput(props: RenderInputComponentProps) {
    const { key, ...inputProps } = props as any;
    inputProps.className = classNames('form-control', inputProps.className);
    return <input key={key} {...inputProps} />;
}

export default DefaultInput;
