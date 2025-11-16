import { useCallback } from 'react';
import type { ChangeEvent, FormEvent } from 'react';

import { fireEvent } from '@root/components/Component';
import MarkdownInput from '@root/components/Form/MarkdownInput';
import TengwarInput from '@root/components/Form/TengwarInput';
import TextIcon from '@root/components/TextIcon';

import type { IProps } from './InformationForm._types';

function InformationForm(props: IProps) {
    const {
        introduction,
        nickname,
        tengwar,

        onIntroductionChange,
        onNicknameChange,
        onTengwarChange,

        onSubmit,
    } = props;

    const _onNicknameChange = useCallback((ev: ChangeEvent<HTMLInputElement>) => {
        void fireEvent(null, onNicknameChange, ev.target.value);
    }, [ nickname, onNicknameChange ]);

    const _onSubmit = useCallback((ev: FormEvent<HTMLFormElement>) => {
        ev.preventDefault();
        void fireEvent('InformationForm', onSubmit);
    }, [ onSubmit ]);

    return <form method="post" action="#" onSubmit={_onSubmit}>
        <div className="form-group">
            <label htmlFor="ed-author-nickname" className="control-label">Nickname</label>
            <input type="text"
                    className="form-control"
                    name="ed-author-nickname"
                    onChange={_onNicknameChange}
                    value={nickname}
            />
        </div>
        <div className="form-group">
            <label htmlFor="ed-author-tengwar" className="control-label">Tengwar</label>
            <TengwarInput name="ed-author-tengwar"
                            onChange={onTengwarChange}
                            originalText={nickname}
                            value={tengwar}
            />
        </div>
        <div className="form-group">
            <label htmlFor="ed-author-profile" className="control-label">Introduction</label>
            <MarkdownInput name="ed-author-profile"
                            rows={15}
                            onChange={onIntroductionChange}
                            value={introduction}
            />
        </div>
        <div className="text-center">
            <button type="submit" className="btn btn-primary">
                <TextIcon icon="ok" /> Save changes
            </button>
        </div>
    </form>;
}

export default InformationForm;
