import StaticAlert from '@root/components/StaticAlert';
import TextIcon from '@root/components/TextIcon';

function NoSentencesAlert() {
    return <StaticAlert type="info">
    <TextIcon icon="info-sign" />{' '}
        <strong>Complete at least one sentence</strong>. Once you have written your first sentence, this form will
        become available.
    </StaticAlert>;
}

export default NoSentencesAlert;
