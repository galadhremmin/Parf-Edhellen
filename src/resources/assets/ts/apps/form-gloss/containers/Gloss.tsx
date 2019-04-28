import React from 'react';

function Gloss(props: any) {
    return <form>
        <div className="form-group">
            <label htmlFor="exampleInputEmail1">Email address</label>
            <input type="email" className="form-control" id="exampleInputEmail1" placeholder="Email" />
        </div>
        <div className="form-group">
            <label htmlFor="exampleInputPassword1">Password</label>
            <input type="password" className="form-control" id="exampleInputPassword1" placeholder="Password" />
        </div>
        <div className="form-group">
            <label htmlFor="exampleInputFile">File input</label>
            <input type="file" id="exampleInputFile" />
            <p className="help-block">Example block-level help text here.</p>
        </div>
        <div className="checkbox">
            <label>
                <input type="checkbox" /> Check me out
            </label>
        </div>
        <button type="submit" className="btn btn-default">Submit</button>
    </form>;
}

export default Gloss;
