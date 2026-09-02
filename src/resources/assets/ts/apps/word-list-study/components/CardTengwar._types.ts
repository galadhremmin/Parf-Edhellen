import type { ITengwarText } from '@root/connectors/backend/IWordListApi';

export interface IProps {
  tengwar: ITengwarText | null;
  as?: keyof JSX.IntrinsicElements;
}
