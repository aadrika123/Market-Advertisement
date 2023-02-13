import React from 'react'
import { Link } from 'react-router-dom'

function Sidebar() {

    const menues = [
        { "id": 0, "name": "Lodge Hostel", "path": "/lodge-hostel" },
        { "id": 1, "name": "Banquet Marriage", "path": "/banquet-marriage" },
        { "id": 2, "name": "Dharamshala", "path": "/dharamshala" },
        { "id": 3, "name": "Advertisement", "path": "/advertDashboard" },
        { "id": 4, "name": "Agency Dashboard", "path": "/agencyDashboard" },
    ]

    return (
        <>
            <div className=' bg-white shadow-lg  h-screen'>
                <div>
                    <img src='https://seeklogo.com/images/G/government-of-jharkhand-logo-D9985104A5-seeklogo.com.png' className='w-28 mx-auto p-2' />
                    <h1 className='text-center font-semibold text-lg '>Government Of Jharkhand</h1>
                </div>

                <div className='ml-12 p-4'>
                    {
                        menues.map((item, i) => (
                            <div key={i}>
                                <div className='flex'>
                                    <img src='https://cdn-icons-png.flaticon.com/512/1828/1828673.png' className='h-5 mt-3' />
                                    <p className='py-2 ml-4'>
                                        <Link className='' to={item.path}>{item.name}</Link>
                                    </p>
                                </div>
                            </div>
                        ))
                    }
                </div>

            </div>
        </>
    )
}

export default Sidebar