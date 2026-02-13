import './BouncingArrow.scss';

const BouncingArrow = (props: any) => (
    <div {...props} className="bouncing-arrow">
        <span className="bouncing-arrow__hand" />
        <span className="bouncing-arrow__label">scroll down</span>
    </div>
);

export default BouncingArrow;
