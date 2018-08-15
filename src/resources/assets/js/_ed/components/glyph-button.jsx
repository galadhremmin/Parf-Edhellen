import React from 'react';
import {
    Button,
    Glyphicon
} from 'react-bootstrap';

const GlyphButton = props => <Button {...props.buttonProps} className="ed-icon-button">
    <Glyphicon glyph={props.glyph} />
    <span>{props.children}</span>
</Button>;

export default GlyphButton;
