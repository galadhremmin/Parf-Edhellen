type BootstrapIcons = 'arrow-down' | 'arrow-up' | 'bell' | 'chevron-down' | 'chevron-up' | 'chevron-left' |
    'chevron-right' | 'comment' | 'edit' | 'envelope' | 'exclamation-sign'| 'info-sign' | 'globe' | 'ok' |
    'pushpin' | 'refresh' | 'remove' | 'search' | 'share' | 'thumbs-down' | 'thumbs-up' |
    'trash' | 'warning-sign' | 'minus-sign' | 'plus-sign' | 'move';

export interface IProps {
    className?: string;
    icon: BootstrapIcons;
}
