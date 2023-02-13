import axios from "axios";
import React, { Component, useEffect, useRef, useState } from "react";
import { Link } from "react-router-dom";
import AdvertisementApiList from "../../../../Compnents/AdvertisementApiList";
import ApiHeader from "../../../../Compnents/ApiHeader";
import Loader from "../../Loader";
import AgencyNotification from "../AgencyNotification";
import { BarGraphComponent } from "../BarGraphComponent";
import { PieChartComponent } from "../PieChartComponent";
import HoardingApprovedApplication from "./HoardingApprovedApplication";
import HoardingPendingList from "./HoardingPendingList";
import HoardingRejectedApplication from "./HoardingRejectedApplication";


function AgencyDashboard() {


    const { api_getAgencyDashboardData } = AdvertisementApiList()
    const [tabIndex, settabIndex] = useState(0)
    const [show, setshow] = useState(false)
    const [agencyDashboard, setagencyDashboard] = useState()
    const myRef = useRef(null);
    const useMountEffect = fun => useEffect(fun, []);
    const executeScroll = () => {
        // console.log("id of div",e.target)
        myRef.current.scrollIntoView();
        // settabIndex(2)
    }
    // run this function from an event handler or pass it to useEffect to execute scroll
    useMountEffect(executeScroll); // Scroll on mount

    const showLoader = (val) => {
        setshow(val);
    }



    ///////////{*** get agencty details ***}/////////
    useEffect(() => {
        getAgencyDetails()
    }, [])
    const getAgencyDetails = () => {
        // props.showLoader(true);
        const requestBody = {
            // deviceId: "selfAdvert",
        }
        axios.post(`${api_getAgencyDashboardData}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('agency dashboard---2', response.data.data)
                setagencyDashboard(response.data.data)
                // setTimeout(() => {
                //     props.showLoader(false);
                // }, 500);
            })
            .catch(function (error) {
                console.log('errorrr.... ', error);
                // setTimeout(() => {
                //     props.showLoader(false);
                // }, 500);

            })
    }

    console.log("agency count data", (agencyDashboard?.countApprovedAppl?.Feb2023) + (agencyDashboard?.countApprovedAppl?.Jan2023))


    return (
        <>
            <Loader show={show} />
            <div className="grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 container  mx-auto ">
                <div className="col-span-6">
                    <div className="bg-white rounded leading-5 shadow-lg">
                        <div className="grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 p-2">
                            <div className="col-span-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-48 h-48 text-violet-200 ">
                                    <path d="M13.5 4.06c0-1.336-1.616-2.005-2.56-1.06l-4.5 4.5H4.508c-1.141 0-2.318.664-2.66 1.905A9.76 9.76 0 001.5 12c0 .898.121 1.768.35 2.595.341 1.24 1.518 1.905 2.659 1.905h1.93l4.5 4.5c.945.945 2.561.276 2.561-1.06V4.06zM18.584 5.106a.75.75 0 011.06 0c3.808 3.807 3.808 9.98 0 13.788a.75.75 0 11-1.06-1.06 8.25 8.25 0 000-11.668.75.75 0 010-1.06z" />
                                    <path d="M15.932 7.757a.75.75 0 011.061 0 6 6 0 010 8.486.75.75 0 01-1.06-1.061 4.5 4.5 0 000-6.364.75.75 0 010-1.06z" />
                                </svg>

                            </div>
                            <div className="col-span-2 p-3 ">

                                <h1 className="font-bold text-3xl text-gray-600 ">Agency Dashboard</h1>
                                <h1 className="text-md text-gray-500 mt-2 p-1  ">Lorem ipsum dolor sit amet consectetur adipisicing elit. Labore cum nam error quia, id maiores vero, suscipit blanditiis cupiditate reiciendis praesentium illum voluptate? </h1>
                                <span className=""></span>
                                <Link to='/hoarding'>
                                    <button className="float-right mt-4 py-2 px-4 inline-block text-center shadow-lg  rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0">Apply Hoarding</button>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="col-span-6">
                    <div >
                        <div className="grid grid-cols-2 md:grid-cols-1 lg:grid-cols-2 gap-4">
                            <div className="flex flex-row p-3 bg-white rounded leading-5 shadow-lg" >
                                <div className="">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 p-4  rounded-full text-pink-500 bg-pink-100 dark:bg-pink-900 dark:bg-opacity-40 ">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3" />
                                    </svg>
                                </div>
                                <div className="ml-3 p-2">
                                    <h1 className="text-gray-500 text-lg font-semibold">Total Hoarding</h1>
                                    <h1 className="text-gray-500 text-lg ">0</h1>
                                </div>
                            </div>
                            <div className="flex flex-row p-3 bg-white rounded leading-5 shadow-lg" onClick={() => settabIndex(1)}>
                                <div className="">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 p-4 rounded-full text-indigo-500 bg-indigo-100 dark:bg-indigo-900  dark:bg-opacity-40 ">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3" />
                                    </svg>
                                </div>
                                <div className="ml-3 p-2">
                                    <h1 className="text-gray-500 text-lg font-semibold">Pending Applications</h1>
                                    <h1 className="text-gray-500 text-lg ">0</h1>
                                </div>
                            </div>
                        </div>
                        <div className="grid grid-cols-2 md:grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
                            <div id="2" className="flex flex-row p-3 bg-white rounded leading-5 shadow-lg" onClick={executeScroll}>
                                <div className="">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 p-4 rounded-full text-green-500 bg-green-100 dark:bg-green-900 dark:bg-opacity-40  ">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3" />
                                    </svg>
                                </div>
                                <div className="ml-3 p-2">
                                    <h1 className="text-gray-500 text-lg font-semibold">Approved Applications</h1>
                                    <h1 className="text-gray-500 text-lg ">0</h1>
                                </div>
                            </div>
                            <div className="flex flex-row p-3 bg-white rounded leading-5 shadow-lg" onClick={executeScroll}>
                                <div className="">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 p-4 rounded-full text-yellow-500 bg-yellow-100 dark:bg-yellow-900 dark:bg-opacity-40">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3" />
                                    </svg>
                                </div>
                                <div className="ml-3 p-2">
                                    <h1 className="text-gray-500 text-lg font-semibold">Rejected Applications</h1>
                                    <h1 className="text-gray-500 text-lg ">0</h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* notification */}
            <div className="grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 container mt-4 mx-auto ">
                <div className="col-span-8">
                    <div className="grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4">

                        {/* graph */}
                        <div className="col-span-6 ">
                            <div className="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 bg-white rounded leading-5 shadow-lg ">
                                <div className="p-1  opacity-95"><BarGraphComponent /></div>
                            </div>
                            <div className="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 bg-red-300 rounded leading-5 shadow-lg mt-4 ">
                                {/* <div className=" opacity-90"><PieChartComponent /></div> */}
                                {/* <HoardingPendingList /> */}
                            </div>
                        </div>

                        <div className="col-span-6 ">
                            { /* /////////// Monthly statics group /////////// */}
                            <div className="rounded leading-5 shadow-lg">
                                <div className="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-4 ">
                                    <div className="col-span-2 flex bg-white  p-4">
                                        <div className="flex-1"><h1 className="text-lg font-semibold text-gray-500">Monthly Statics</h1></div>
                                        <div className="flex-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 float-right text-indigo-500">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div className="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2">
                                    <div className="flex flex-row p-3 bg-white ">
                                        <div className="">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 p-4  rounded-full text-pink-500 bg-pink-100 dark:bg-pink-900 dark:bg-opacity-40 ">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3" />
                                            </svg>
                                        </div>
                                        <div className="ml-3 p-2">
                                            <h1 className="text-gray-500 text-lg font-semibold">Total Hoarding</h1>
                                            <h1 className="text-gray-500 text-lg ">0</h1>
                                        </div>
                                    </div>
                                    <div className="flex flex-row p-3 bg-white ">
                                        <div className="">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 p-4  rounded-full text-green-500 bg-green-100 dark:bg-green-900 dark:bg-opacity-40 ">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3" />
                                            </svg>
                                        </div>
                                        <div className="ml-3 p-2">
                                            <h1 className="text-gray-500 text-lg font-semibold">Total Renewal</h1>
                                            <h1 className="text-gray-500 text-lg ">0</h1>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            { /* /////////// renew list /////////// */}
                            <div className="  flex bg-pink-500 rounded leading-5 shadow-lg mt-4">
                                <div className="">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-16 h-16 p-4   text-white ">
                                        <path fill-rule="evenodd" d="M2.625 6.75a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0zm4.875 0A.75.75 0 018.25 6h12a.75.75 0 010 1.5h-12a.75.75 0 01-.75-.75zM2.625 12a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0zM7.5 12a.75.75 0 01.75-.75h12a.75.75 0 010 1.5h-12A.75.75 0 017.5 12zm-4.875 5.25a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0zm4.875 0a.75.75 0 01.75-.75h12a.75.75 0 010 1.5h-12a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div className="">
                                    <h1 className=" float-right mt-4 text-white text-lg font-semibold">List of application to be renew</h1>
                                </div>
                            </div>

                            { /* /////////// Yearly statics group /////////// */}
                            <div className="rounded leading-5 shadow-lg">
                                <div className="grid grid-cols-4 md:grid-cols-2 lg:grid-cols-2 gap-4 mt-4 ">
                                    <div className="col-span-2 flex bg-white p-4">
                                        <div className="flex-1"><h1 className="text-lg font-semibold text-gray-500">Yearly Statics</h1></div>
                                        <div className="flex-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 float-right text-indigo-500">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div className="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2">
                                    <div className="flex flex-row p-3 bg-white">
                                        <div className="">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 p-4  rounded-full text-pink-500 bg-pink-100 dark:bg-pink-900 dark:bg-opacity-40 ">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3" />
                                            </svg>
                                        </div>
                                        <div className="ml-3 p-2">
                                            <h1 className="text-gray-500 text-lg font-semibold">Total Hoarding</h1>
                                            <h1 className="text-gray-500 text-lg ">0</h1>
                                        </div>
                                    </div>
                                    <div className="flex flex-row p-3 bg-white ">
                                        <div className="">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 p-4  rounded-full text-green-500 bg-green-100 dark:bg-green-900 dark:bg-opacity-40 ">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3" />
                                            </svg>
                                        </div>
                                        <div className="ml-3 p-2">
                                            <h1 className="text-gray-500 text-lg font-semibold">Total Renewal</h1>
                                            <h1 className="text-gray-500 text-lg ">0</h1>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div className={`${tabIndex == 1 ? 'bg-indigo-300 z-20 drop-shadow-xl overflow-auto ' : 'bg-white'} transition-all ease-in-out `}  >
                        <h1 className={`${tabIndex == 1 ? 'text-white' : 'text-gray-500'} text-lg font-semibold px-2 mt-4 border-b`}>Pending Applications</h1>
                        <div className="grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12  rounded leading-5 shadow-md ">
                            <HoardingPendingList showLoader={showLoader} />
                        </div>
                    </div>

                    <div ref={myRef} className={`${tabIndex == 2 ? 'bg-indigo-300 z-20 drop-shadow-xl' : 'bg-white'} transition-all ease-in-out `}  >
                        <h1 className={`${tabIndex == 2 ? 'text-white' : 'text-gray-500'} text-lg font-semibold px-2 mt-4 border-b`}>Approved Applications</h1>
                        <div className="grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12  rounded leading-5 shadow-md ">
                            <HoardingApprovedApplication showLoader={showLoader} />
                        </div>
                    </div>
                    <div ref={myRef} className={`${tabIndex == 3 ? 'bg-indigo-300 z-20 drop-shadow-xl' : 'bg-white'} transition-all ease-in-out `}  >
                        <h1 className={`${tabIndex == 3 ? 'text-white' : 'text-gray-500'} text-lg font-semibold px-2 mt-4 border-b`}>Rejected Applications</h1>
                        <div className="grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12  rounded leading-5 shadow-md ">
                            <HoardingRejectedApplication showLoader={showLoader} />
                        </div>
                    </div>

                </div>
                <div className="col-span-4 bg-white rounded leading-5 shadow-lg  h-screen" >
                    <AgencyNotification />
                </div>
            </div>
        </>
    )

}

export default AgencyDashboard