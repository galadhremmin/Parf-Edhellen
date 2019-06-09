import React from 'react';

import { fireEvent } from '@root/components/Component';
import { IComponentProps } from '@root/components/Form/FormComponent._types';
import { IGlossDetail } from '@root/connectors/backend/GlossResourceApiConnector._types';

const _setOrder = (newValue: IGlossDetail[]) => {
    newValue.forEach((detail, i) => {
        detail.order = (i + 1) * 10;
    });
};

const _createNewValue = (detail: IGlossDetail, source: number, value: IGlossDetail[]) => {
    return [
        ...value.slice(0, source),
        detail,
        ...value.slice(source + 1),
    ];
};

function GlossDetailInput(props: IComponentProps<IGlossDetail[]>) {
    const {
        name,
        onChange,
        value,
    } = props;

    const _onAddClick = (ev: React.MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();
        const newValue: IGlossDetail[] = [
            ...value,
            {
                category: '',
                order: 0,
                text: '',
            },
        ];
        _setOrder(newValue);

        fireEvent(name, onChange, newValue);
    };

    const _onMoveClick = (source: number, direction: number) => (ev: React.MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();

        const destination = source + direction;
        if (destination < 0 || destination >= value.length) {
            return;
        }

        const newValue = value.map((detail, i) => {
            let newDetail = detail;
            if (source === i) {
                newDetail = value[destination];
            } else if (destination === i) {
                newDetail = value[source];
            }

            return {
                ...newDetail,
                order: (i + 1) * 10,
            };
        });

        fireEvent(name, onChange, newValue);
    };

    const _onDeleteClick = (source: number) => (ev: React.MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();

        const newValue = value.filter((v, i) => i !== source);
        _setOrder(newValue);

        fireEvent(name, onChange, newValue);
    };

    const _onDetailChange = (source: number, property: keyof IGlossDetail) =>
        (ev: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const {
            value: textValue,
        } = ev.target;

        const detail = value[source];
        detail[property] = textValue;

        const newValue = _createNewValue(detail, source, value);
        fireEvent(name, onChange, newValue);
    };

    return <>
            {value.map((detail, i) => <div key={detail.order} className="form-group row">
            <div className="col-sm-3">
                <input type="text"
                    className="form-control"
                    id={`ed-gloss-detail-title-${detail.order}`}
                    onChange={_onDetailChange(i, 'category')}
                    placeholder="Title"
                    required={true}
                    value={detail.category}
                />
                <div className="btn-group btn-group-xs" role="group">
                    <button type="button" className="btn btn-default" onClick={_onMoveClick(i, -1)}>
                        <span className="glyphicon glyphicon-arrow-up"></span>
                    </button>
                    <button type="button" className="btn btn-default" onClick={_onDeleteClick(i)}>
                        <span className="glyphicon glyphicon-remove"></span>
                    </button>
                    <button type="button" className="btn btn-default" onClick={_onMoveClick(i, 1)}>
                        <span className="glyphicon glyphicon-arrow-down"></span>
                    </button>
                </div>
            </div>
            <div className="col-sm-9">
                <textarea className="form-control"
                        id={`ed-gloss-detail-text-${detail.order}`}
                        rows={5}
                        onChange={_onDetailChange(i, 'text')}
                        required={true}
                        value={detail.text}
                />
            </div>
        </div>)}
        {(value.length === 0 || value[value.length - 1].text.length > 0) && <div className="text-center">
            <button className="btn btn-default"
                    onClick={_onAddClick}>Add details</button>
        </div>}
    </>;
}

GlossDetailInput.defaultProps = {
    value: [],
} as Partial<IComponentProps<IGlossDetail[]>>;

export default GlossDetailInput;
