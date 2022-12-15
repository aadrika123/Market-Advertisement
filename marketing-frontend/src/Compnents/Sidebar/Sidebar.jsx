import React from 'react'
import { Link } from 'react-router-dom'

function Sidebar() {

    const menues = [
        { "id": 0, "name": "Lodge Hostel", "path": "/lodge-hostel" },
        { "id": 1, "name": "Banquet Marriage", "path": "/banquet-marriage" },
        { "id": 2, "name": "Dharamshala", "path": "/dharamshala" },
    ]

    return (
        <>
            <div className='w-full bg-white shadow-lg h-screen'>
                <div className='ml-5 pt-20'>
                    {
                        menues.map((item, i) => (
                            <div key={i}>
                                <p className='py-2'>
                                    <Link className='' to={item.path}>{item.name}</Link>
                                </p>
                            </div>
                        ))
                    }
                </div>

            </div>
        </>
    )
}

export default Sidebar