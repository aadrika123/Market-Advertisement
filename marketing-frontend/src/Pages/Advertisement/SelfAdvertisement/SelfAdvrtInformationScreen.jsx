import { Info } from '@mui/icons-material'
import React from 'react'

function SelfAdvrtInformationScreen() {

    let tittleStyle = 'text-gray-800 text-xs font-bold'
    let labelStyle = 'text-xs text-gray-500 p-1 -mt-1 '
    let divStyle = ' px-2 shadow-sm mt-3 p-2'
    return (
        <>
            <div className='border border-dashed border-violet-800 bg-white mt-[4.8rem] p-6'>
                <div className='flex flex-row'>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-violet-800">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <h1 className='ml-2 text-lg'>Information</h1>
                </div>
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Ulb :-</span>
                    <span className={`${labelStyle} flex-1 `}>First select ulb then proceed forward </span>
                </div>
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Licence Year :-</span>
                    <span className={`${labelStyle} flex-1 `} >License year will insure the validity of your license .</span>
                </div>
                {/* <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Applicant Name :-</span>
                    <span className={`${labelStyle} flex-1 `}>owner of the licence .</span>
                </div>
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Father Name :-</span>
                    <span className={`${labelStyle} flex-1 `}>Father name of the applicant .</span>
                </div>
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>E-mail :-</span>
                    <span className={`${labelStyle} flex-1 `}>Email of the applicant to contact through mail .</span>
                </div> */}
                {/* <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Address  :-</span>
                    <span className={`${labelStyle} flex-1 `}>Address of the applicant for detail information .</span>
                </div>
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Ward No  :-</span>
                    <span className={`${labelStyle} flex-1 `}>Ward no of the address to insure the address .</span>
                </div> */}
                {/* <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Mobile No. :-</span>
                    <span className={`${labelStyle} flex-1 `}>Mobile no to make contact with the applicant easier .</span>
                </div> */}
                {/* <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Aadhar No :-</span>
                    <span className={`${labelStyle} flex-1 `}>Adhar no. tp verify applicant detail .</span>
                </div> */}
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Entity Name :-</span>
                    <span className={`${labelStyle} flex-1 `}>Entity name is the name of your business taking license for .</span>
                </div>
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Entity Address  :-</span>
                    <span className={`${labelStyle} flex-1 `}>To insure address where business is established.</span>
                </div>
                {/* <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Entity Ward No.  :-</span>
                    <span className={`${labelStyle} flex-1 `}>To insure ward no. of entity address.</span>
                </div> */}

                {/* <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Installation Location  :-</span>
                    <span className={`${labelStyle} flex-1 `}>Exact location of your business .</span>
                </div> */}
                {/* <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Brand Display Name  :-</span>
                    <span className={`${labelStyle} flex-1 `}></span>
                </div> */}
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Holding No.  :-</span>
                    <span className={`${labelStyle} flex-1 `}>Holding of your addrress where business is established</span>
                </div>
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Trade License No.  :-</span>
                    <span className={`${labelStyle} flex-1 `}>To insure that your business is illegaly approved .</span>
                </div>
                {/* <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>GST No:-</span>
                    <span className={`${labelStyle} flex-1 `}></span>
                </div> */}
                {/* <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Display Area :-</span>
                    <span className={`${labelStyle} flex-1 `}>Display area to insure area in which your brand is diaplaying.</span>
                </div>
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Display Type  :-</span>
                    <span className={`${labelStyle} flex-1 `}>Display Type to insure the type .</span>
                </div> */}
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Longitude  :-</span>
                    <span className={`${labelStyle} flex-1 `}>Longitude for the location of your business.</span>
                </div>
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Latitude   :-</span>
                    <span className={`${labelStyle} flex-1 `}>Latitude for the location of your business.</span>
                </div>
            </div>
        </>
    )
}

export default SelfAdvrtInformationScreen