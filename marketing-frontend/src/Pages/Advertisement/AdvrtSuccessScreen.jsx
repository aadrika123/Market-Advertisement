import React from 'react'
import { Link } from 'react-router-dom'

function AdvrtSuccessScreen(props) {

    console.log("data in response screen", props?.responseScreenData)
    return (
        <div>



            <div className='   '>
                <div className=' w-2/6 mx-auto  bg-white p-12'>
                    <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-1 mx-auto '>
                        <div>
                            <img src='https://img.freepik.com/free-vector/mail-sent-concept-illustration_114360-96.jpg?w=740&t=st=1672636866~exp=1672637466~hmac=5f164187014737ee6e91346184ade8b9590a40d01e247f97d5dba6f313d4c474' className='h-44 mx-auto' />
                        </div>
                        <div className='ml-32 -mt-10'>
                            <img src='https://cdn-icons-png.flaticon.com/512/5610/5610944.png' className='h-12 -mt-4 mx-auto opacity-75' />
                            {/* <img src='https://cdn-icons-png.flaticon.com/512/753/753344.png' className='h-12 mx-auto opacity-75' /> */}
                        </div>
                        <div>
                            <h1 className='text-center text-sm text-gray-600'>Application Submitted Successfully</h1>
                        </div>
                        <div>
                            <h1 className='text-center text-sm text-gray-500 font-bold'> Application No. :- {props?.responseScreenData?.ApplicationNo}</h1>
                        </div>
                        <div className='mx-auto flex flex-row space-x-4'>
                            <Link to='/advertDashboard'>
                                <button class="w-32 text-xs p-3 mt-10 font-medium tracking-wide text-white capitalize transition-colors duration-200 transform bg-sky-400 rounded-md hover:bg-sky-600 focus:outline-none focus:bg-sky-600" onClick={() => navigate(`/selfAdvrt`)}>
                                    back
                                </button>
                            </Link>
                            {/* <button class="w-32 text-xs mt-10 font-medium tracking-wide text-white capitalize transition-colors duration-200 transform bg-sky-400 rounded-md hover:bg-sky-600 focus:outline-none focus:bg-sky-600" onClick={() => navigate('/dashboardEntry')}>
                                Dashboard
                            </button> */}
                        </div>

                    </div>

                </div>

            </div>
        </div>
    )
}

export default AdvrtSuccessScreen