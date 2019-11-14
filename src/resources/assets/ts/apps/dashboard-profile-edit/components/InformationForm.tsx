import React, { useCallback } from 'react';

import { fireEvent } from '@root/components/Component';
import MarkdownInput from '@root/components/Form/MarkdownInput';
import TengwarInput from '@root/components/Form/TengwarInput';

import { IProps } from './InformationForm._types';

function InformationForm(props: IProps) {
    const {
        introduction,
        nickname,
        tengwar,

        onIntroductionChange,
        onNicknameChange,
        onTengwarChange,
    } = props;

    const _onNicknameChange = useCallback((ev: React.ChangeEvent<HTMLInputElement>) => {
        fireEvent(null, onNicknameChange, ev.target.value);
    }, [ nickname, onNicknameChange ]);

    return <form className="form-horizontal" method="post" action="http://localhost:8000/author/edit/173">
        <div className="form-group">
            <label htmlFor="ed-author-nickname" className="col-sm-2 control-label">Nickname</label>
            <div className="col-sm-10">
                <input type="text"
                       className="form-control"
                       name="ed-author-nickname"
                       onChange={_onNicknameChange}
                       value={nickname}
                />
            </div>
        </div>
        <div className="form-group">
            <label htmlFor="ed-author-tengwar" className="col-sm-2 control-label">Tengwar</label>
            <div className="col-sm-10">
                <TengwarInput name="ed-author-tengwar"
                              onChange={onTengwarChange}
                              originalText={nickname}
                              value={tengwar}
                />
            </div>
        </div>
        <div className="form-group">
            <label htmlFor="ed-author-profile" className="col-sm-2 control-label">Introduction</label>
            <div className="col-sm-10">
                <MarkdownInput name="ed-author-profile"
                               rows={15}
                               onChange={onIntroductionChange}
                               value={introduction}
                />
            </div>
        </div>
    </form>;
}

export default InformationForm;
