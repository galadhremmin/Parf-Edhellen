import type { IProps } from './Quote._types';

const Quote = (props: IProps) => <>
    &ldquo;{props.children}&rdquo;
</>;

export default Quote;
