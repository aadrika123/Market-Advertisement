import React from 'react'

function DocumentUploadSteps() {

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
                    <h1 className='ml-2 text-xl'>How To Uplaod Document ?</h1>
                </div>
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Step 1 :-</span>
                    <span className={`${labelStyle} flex-1 `} >Choose a file which you want to uplaod </span>
                </div>
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Step 2 :-</span>
                    <span className={`${labelStyle} flex-1 `}>To upload image click on upload button</span>
                </div>
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Step 3 :-</span>
                    <span className={`${labelStyle} flex-1 `}>Click on preview to view uploaded image </span>
                </div>
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Step 4 :-</span>
                    <span className={`${labelStyle} flex-1 `}>Click on Save & Next button to proceed with the application</span>
                </div>
                {/* <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Trade License No.  :-</span>
                    <span className={`${labelStyle} flex-1 `}>To insure that your business is illegaly approved .</span>
                </div>
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Longitude  :-</span>
                    <span className={`${labelStyle} flex-1 `}>Longitude for the location of your business.</span>
                </div>
                <div className={`${divStyle}flex mt-3`}>
                    <span className={`${tittleStyle} flex-1 `}>Latitude   :-</span>
                    <span className={`${labelStyle} flex-1 `}>Latitude for the location of your business.</span>
                </div> */}
                <div>
                    <img src='https://img.freepik.com/free-vector/image-upload-concept-illustration_114360-798.jpg?w=826&t=st=1675677630~exp=1675678230~hmac=3ce8cef91d846d7436a3f83357381af891a8e1e3c51eeef330e620448f1420c8' className='opacity-90 h-80 mx-auto' />
                </div>
            </div>
        </>
    )
}

export default DocumentUploadSteps