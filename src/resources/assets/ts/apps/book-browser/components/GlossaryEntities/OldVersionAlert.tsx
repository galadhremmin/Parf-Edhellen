import TextIcon from '@root/components/TextIcon';
import type { IProps } from './OldVersionAlert._types';

const OldVersionAlert = (props: IProps) => {
    const { lexicalEntry } = props;

    if (lexicalEntry.isLatest) {
        return null;
    }

    return <p className="alert alert-danger">
        <TextIcon icon="warning-sign" />
        {' '}
        <strong>Important!</strong> A newer version of this entry was found in the dictionary.
        You should <a href={`/wt/${lexicalEntry.id}/latest`}> go to the latest version instead</a>.
    </p>;
};

export default OldVersionAlert;
