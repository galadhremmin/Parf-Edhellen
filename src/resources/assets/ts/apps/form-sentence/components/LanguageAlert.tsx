import StaticAlert from '@root/components/StaticAlert';
import TextIcon from '@root/components/TextIcon';

function LanguageAlert() {
    return <StaticAlert type="info">
        <TextIcon icon="info-sign" />{' '}
        <strong>Select a language</strong>. Once you have selected a language, this portion
        of the form will become available.
    </StaticAlert>;
}

export default LanguageAlert;
