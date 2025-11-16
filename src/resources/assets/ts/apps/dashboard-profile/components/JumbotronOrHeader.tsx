import Jumbotron from '@root/components/Jumbotron';
import type { IProps } from './JumbotronOrHeader._types';

export default function JumbotronOrHeader({
    children,
    backgroundImageUrl,
    className,
    isJumbotron,
}: IProps) {
    if (isJumbotron) {
        return <Jumbotron className={className} backgroundImageUrl={backgroundImageUrl}>{children}</Jumbotron>;
    }

    return <header className={className}>{children}</header>;
}
