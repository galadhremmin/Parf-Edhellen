import React from 'react';

import { fireEvent } from '@root/components/Component';
import { IComponentProps } from '@root/components/Form/FormComponent._types';
import TextIcon from '@root/components/TextIcon';
import { IGlossDetail } from '@root/connectors/backend/IGlossResourceApi';

const _setOrder = (newValue: IGlossDetail[]) => {
    newValue.forEach((detail, i) => {
        detail.order = (i + 1) * 10;
    });
};

const _createNewValue = (detail: IGlossDetail, sourceIndex: number, value: IGlossDetail[]) => {
    return [
        ...value.slice(0, sourceIndex),
        detail,
        ...value.slice(sourceIndex + 1),
    ];
};

function GlossDetailInput(props: IComponentProps<IGlossDetail[]>) {
    const {
        name,
        onChange,
        value = [],
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

        void fireEvent(name, onChange, newValue);
    };

    const _onMoveClick = (sourceIndex: number, direction: number) => (ev: React.MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();

        const destination = sourceIndex + direction;
        if (destination < 0 || destination >= value.length) {
            return;
        }

        const newValue = value.map((detail, i) => {
            let newDetail = detail;
            if (sourceIndex === i) {
                newDetail = value[destination];
            } else if (destination === i) {
                newDetail = value[sourceIndex];
            }

            return {
                ...newDetail,
                order: (i + 1) * 10,
            };
        });

        void fireEvent(name, onChange, newValue);
    };

    const _onDeleteClick = (sourceIndex: number) => (ev: React.MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();

        const newValue = value.filter((v, i) => i !== sourceIndex);
        _setOrder(newValue);

        void fireEvent(name, onChange, newValue);
    };

    const _onDetailChange = (sourceIndex: number, propertyName: keyof Pick<IGlossDetail, "text" | "category">) =>
        (ev: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const {
            value: textValue,
        } = ev.target;

        const detail = value[sourceIndex];
        detail[propertyName] = textValue;

        const newValue = _createNewValue(detail, sourceIndex, value);
        void fireEvent(name, onChange, newValue);
    };

    return <>
            {value.map((detail, i) => <div key={detail.order} className="form-group row">
            <div className="col-sm-3 d-flex flex-column">
                <input type="text"
                    className="form-control"
                    id={`ed-gloss-detail-title-${detail.order}`}
                    onChange={_onDetailChange(i, 'category')}
                    placeholder="Title"
                    required={true}
                    value={detail.category}
                />
                <div className="btn-group btn-group-sm" role="group">
                    <button type="button" className="btn btn-secondary" onClick={_onMoveClick(i, -1)}>
                        <TextIcon icon="arrow-up" />
                    </button>
                    <button type="button" className="btn btn-secondary" onClick={_onDeleteClick(i)}>
                        <TextIcon icon="remove" />
                    </button>
                    <button type="button" className="btn btn-secondary" onClick={_onMoveClick(i, 1)}>
                    <TextIcon icon="arrow-down" />
                    </button>
                </div>
            </div>
            <div className="col-sm-9">
                <textarea className="form-control"
                        id={`ed-gloss-detail-text-${detail.order}`}
                        rows={5}
                        onChange={_onDetailChange(i, 'text')}
                        required={true}
                        placeholder="Details go here. Markdown is supported."
                        value={detail.text}
                />
            </div>
        </div>)}
        {(value.length === 0 || value[value.length - 1].text.length > 0) && <div className="text-end">
            <button className="btn btn-secondary"
                    type="button"
                    onClick={_onAddClick}>Add details</button>
        </div>}
    </>;
}

export default GlossDetailInput;
