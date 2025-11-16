import type { IProps } from './PaginationDetails._types';

function PaginationDetails(props: IProps) {
    let {
        currentPage,
        numberOfPages,
        numberOfPosts,
        numberOfTotalPosts,
    } = props;

    const {
        originalPostIsAlwaysVisible = true,
    } = props;

    // clamp to 0 < x < inf
    currentPage = Math.max(1, currentPage);
    numberOfPages = Math.max(1, numberOfPages);

    // This assumes that the original post is always included on the page, regardless of the number of posts there is.
    // If this behaviour changes, this must also be adjusted!
    // <note>
    if (originalPostIsAlwaysVisible) {
        numberOfTotalPosts = Math.max(0, numberOfTotalPosts - 1);
        numberOfPosts = Math.max(0, numberOfPosts - 1);
    }
    // </note>

    // this is the actual math to establish positioning within the thread
    const pageSize = Math.ceil(numberOfTotalPosts / Math.max(numberOfPages, 1));
    const offsetBegin = numberOfTotalPosts === 0 ? 0 : Math.max(0, currentPage - 1)*pageSize + 1;
    const offsetEnd = Math.min(numberOfTotalPosts, currentPage*pageSize);
    
    return <aside className="discuss-body--pagination-details" role="note">
        Viewing {numberOfPosts} of {numberOfTotalPosts} replies - {offsetBegin} through {offsetEnd} (page {currentPage} of {numberOfPages})
    </aside>;
}

export default PaginationDetails;
