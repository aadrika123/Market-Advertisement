import React, { useEffect, useState } from 'react'

function AdvertisementStepper({ colorCode, currentStep }) {

    const [stepOneDone, setStepOneDone] = useState(0)
    const [stepTwoDone, setStepTwoDone] = useState(0)
   
    const [currentPage1, setCurrentPage1] = useState("font-medium")
    const [currentPage2, setCurrentPage2] = useState("font-medium")

    const [currentPage1Border, setCurrentPage1Border] = useState("")
    const [currentPage2Border, setCurrentPage2Border] = useState("")
 
    const [lineOne, setLineOne] = useState("border-gray-300")
 


    useEffect(() => {

        if (currentStep == 1) {
            setCurrentPage1("font-bold bg-white border border-red-200 rounded")
            setCurrentPage1Border("border-dotted")
            setCurrentPage2("font-medium")

        } if (currentStep == 2) {
            setCurrentPage2("font-bold bg-white border border-red-200 rounded")
            setCurrentPage2Border("border-dotted")
            setCurrentPage1("font-medium")

        }
    }, [currentStep])


    const colorValue = colorCode
    useEffect(() => {

        if (colorValue == 1) {
            setStepOneDone("text-white bg-teal-600");
            setLineOne("border-teal-600")
        } else if (colorValue == 2) {
            setStepOneDone("text-white bg-teal-600");
            setStepTwoDone("text-white bg-teal-600");
            setLineOne("border-teal-600")
            setLineTwo("border-teal-600")
        }
       

    }, [colorValue])
    return (
        <>
            <div class="md:mx-6 md:p-4 md:m-2">
                <div class="flex items-center ">
                    <div class="flex items-center text-gray-500 relative">
                        <div class={`${stepOneDone} ${currentPage1Border} rounded-full transition duration-500 ease-in-out h-10 w-10 py-3 border-2 border-teal-600 `}>
                            <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bookmark ">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div className={` ${currentPage1} absolute top-0 -ml-10 text-center mt-12 w-32 text-xs uppercase text-teal-600`}>Details </div>
                    </div>
                    <div class={`flex-auto border-t-2 transition duration-500 ease-in-out ${lineOne} `}></div>
                    <div class="flex items-center text-gray-500 relative">
                        <div class={` ${stepTwoDone} ${currentPage2Border} rounded-full transition duration-500 ease-in-out h-10 w-10 py-3 border-2 border-teal-600`}>
                            <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-plus ">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7" r="4"></circle>
                                <line x1="20" y1="8" x2="20" y2="14"></line>
                                <line x1="23" y1="11" x2="17" y2="11"></line>
                            </svg>
                        </div>
                        <div className={` ${currentPage2} absolute top-0 -ml-10 text-center mt-12 w-32 text-xs uppercase text-teal-600`}>Upload Document </div>
                    </div>
                    <span className='text-xs md:text-md text-violet-700 text-center pl-4 transition-all animate-wiggle '>&emsp; <strong className='text-2xl text-violet-800 '> {2 - currentStep}
                    </strong>More Screens</span>

                </div>
            </div>
        </>
    )
}

export default AdvertisementStepper