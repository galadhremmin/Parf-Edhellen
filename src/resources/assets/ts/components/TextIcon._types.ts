type BootstrapIcons = 'arrow-down' | 'arrow-up' | 'bell' | 'chevron-down' | 'chevron-up' |
    'comment' | 'edit' | 'envelope' | 'exclamation-sign'| 'info-sign' | 'globe' | 'ok' | 'pencil' |
    'refresh' | 'remove' | 'remove-sign' | 'search' | 'share' | 'thumbs-down' | 'thumbs-up' |
    'trash' | 'warning-sign';

export interface IProps {
    className?: string;
    icon: BootstrapIcons;
}
