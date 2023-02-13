

import React, { useState, useEffect } from 'react'
//  import QRCode from "react-qr-code";
//  import CitizenApplyApiList from '../../../../Components/CitizenApplyApiList';
import axios from 'axios'
import { AiFillPrinter } from 'react-icons/ai'
import { Link, useNavigate, useParams } from 'react-router-dom';
//  import NonBlockingLoader from '../NonBlockingLoader';
import { useRef } from 'react';
import { QrCode } from '@mui/icons-material';
import BackButton from '../BackButton';
 

class ComponentToPrint extends React.Component {


    render() {

        //  console.log("paymentData...1", this.props?.paymentData)

        return (
   
         <>
             {/* <div>
                 <div className='md:px-0 flex-1 '>
                   
                 </div>
                 <div className='md:px-0 flex-1 '>
                     <button onClick={() => window.print()} className="float-right pl-4 pr-6 py-1 bg-sky-400 text-white font-medium text-xs leading-tight uppercase rounded  hover:bg-amber-100 hover: focus: focus:outline-none focus:ring-0  active: transition duration-150 ease-in-out">
                       
                         print
                     </button>
                 </div>
             </div> */}
             <div id="printableArea" className=''>

                 <div>
                 {/* <BackButton /> */}

                     <div className='border-2 border-dashed border-gray-600  bg-white p-3 w-[200mm] h-auto  md:mx-auto lg:mx-auto container ml-6 mt-12 pb-12'>
                         <div className='grid grid-col-1 md:grid-col-12 lg:grid-col-12 '>
                             <div className=''>
                                 <img src='https:upload.wikimedia.org/wikipedia/commons/thumb/a/a9/Jharkhand_Rajakiya_Chihna.svg/1200px-Jharkhand_Rajakiya_Chihna.svg.png' className='h-16 mx-auto' />
                             </div>
                             <div className=''>
                                 <img src='https:upload.wikimedia.org/wikipedia/commons/thumb/a/a9/Jharkhand_Rajakiya_Chihna.svg/1200px-Jharkhand_Rajakiya_Chihna.svg.png' alt="" className=' w-[22rem] h-[22rem]  absolute z-10 bg-transparent opacity-20 mt-[16rem] ml-[13rem]  rounded-full border' />
                             </div>
                         </div>

                      
                         <div className='grid grid-col-1 md:grid-col-12 lg:grid-col-12 p-2 '>
                             <div className=''>
                                 <h1 className='font-bold text-2xl text-center '>RANCHI MUNICIPAL CORPORATION</h1>
                             </div>
                         </div>

                         {/* holding tax */}
                         <div className='grid grid-col-1 md:grid-col-12 lg:grid-col-12 p-2 '>
                             <div className='mx-auto'>
                                 <h1 className='font-semibold text-xl text-center text-gray-800 border border-gray-700 w-[16rem] uppercase'>APPROVAL FORM</h1>
                             </div>
                         </div>

                         {/* detail section 1 */}
                         <div>
                             <table className='w-full p-2 '>
                                 <tr className=''>
                                     <td className=' '>
                                         <div className='flex p-1 text-md'>
                                             <h1 className='flex text-gray-900 font-sans '>Receipt No. :</h1>
                                             <h1 className='flex font-sans font-semibold  pl-2'> DEMO12345</h1>
                                         </div>

                                     </td>
                                     <td className='float-right '>
                                         <div className='flex p-1 text-md'>
                                             <h1 className='flex text-gray-900 font-sans '>Date : </h1>
                                             <h1 className='flex font-sans font-semibold pl-2 '>03/02/2023</h1>
                                         </div>

                                     </td>
                                 </tr>
                             </table>
                         </div>


                         {/* detail section 2 */}
                         <div>
                             <table className='w-full  p-2'>
                                 <tr className=''>
                                     <td className=' '>
                                         <div className='flex p-1 text-md'>
                                             <h1 className='flex text-gray-900 font-sans '>To ,</h1>
                                             <h1 className='flex font-sans font-semibold  pl-2'>Applicant</h1>
                                         </div>
                                     </td>

                                 </tr>
                             </table>
                         </div>
                         {/* N.B online */}
                         <div className='grid grid-col-1 md:grid-col-12 xl:grid-col-12 p-2 '>
                             <div className=''>
                                 <h1 className=' text-md leading-relaxed'> Please refer to your application No.<span className='font-bold'> 43546576787</span>, dated <span className='font-bold'>04/02/2022</span> for registration of installation of Outdoor Media Device for Display of outdoor Advertisement.
                                 </h1>
                             </div>
                         </div>

                         {/* holding tax details */}
                         <div className='grid grid-col-1 md:grid-col-12 xl:grid-col-12 p-2 ' >
                             <div className=''>
                                 <h1 className='text-md text-left leading-relaxed '>Dear Sir,</h1>
                             </div>
                         </div>

                         {/* Table 1 */}
                         <div>
                             <h1 className='text-md leading-relaxed p-2'>
                                 This is with reference to your application regarding registration with Urban Local Bodies for installation of an Outdoor Media Device for display of outdoor advertisements.
                                 <br />
                                 It is to inform that following decision has been taken in consideration of your application:
                                 <br />
                                 1.  Your application for registrationis approved and unique identification number alloted to you is ........ Please use the same for all future correspondance with the Urban Local Bodies and for activating your account on the website of Urban Local Bodies.
                                 <br />
                                 2.  Your application for new Media/renewal is rejected on account of the following :<br />

                                 a.   Incomplete application.<br />
                                 b.   Incorrect information provided<br />
                                 c.   Pending dues with Municipal Corporation.<br />
                                 d.   Blacklisted Status not verified<br />
                                 e.   Others ..............
                             </h1>
                             <br />
                             <h1 className='text-md leading-relaxed p-2'>
                                 Thanking You,<br />
                                 Municipal Commissioner/Executive Officer/Special Officer,<br />
                                 Urban Local Bodies,
                             </h1>
                             <br />
                             <h1 className='text-md leading-relaxed p-2z'>
                                 Note :   In case of rejection of application you may apply fresh on satisfying the above mentioned conditions. <br />
                                 Note :   This is a Typical format only and is subject to modification/amendment by the Urban Local Bodies from time to time .
                             </h1>
                         </div>


                         {/* Qr code
                         <div>
                             <table className='w-full mt-8  '>
                                 <tr className=''>
                                     <td className=' '>
                                         <div className=''>
                                             <QrCode value={this.props?.qrValue} size='64' />
                                             <QrCode url='https:upload.wikimedia.org/wikipedia/commons/thumb/a/a9/Jharkhand_Rajakiya_Chihna.svg/1200px-Jharkhand_Rajakiya_Chihna.svg.png' size='64' />
                                         </div>
                                         <div className='flex '>
                                             <h1 className='flex text-gray-900 font-sans text-md leading-relaxed'>For Details Please Visit : udhd.jharkhand.gov.in</h1>
                                         </div>
                                         <div className='flex '>
                                             <h1 className='flex text-gray-900 font-sans text-md leading-relaxed'>Or Call us at 1800 8904115 or 0651-3500700</h1>
                                         </div>
                                     </td>
                                     <td className='float-right mt-8  '>
                                         <div className='flex '>
                                             <h1 className='flex text-gray-900 font-sans text-md leading-relaxed'>In Collaboration with</h1>
                                         </div>
                                         <div className='flex'>
                                             <h1 className='flex text-gray-900 font-sans text-md leading-relaxed'>Sri Publication & Stationers Pvt Ltd</h1>
                                         </div>

                                     </td>
                                 </tr>
                             </table>
                         </div>

                         computer generated text
                         <div className='grid grid-col-1 md:grid-col-12 lg:grid-col-12 p-2 '>
                             <div className=''>
                                 <h1 className='font-semibold text-md text-center'>**This is a computer-generated receipt and it does not require a physical signature.**</h1>
                             </div>
                         </div>

                         swatch bharat logo
                         <div className='grid grid-col-1 md:grid-col-12 lg:grid-col-12 p-2  '>
                             <div className=''>
                                 <img src='https:zeevector.com/wp-content/uploads/LOGO/Swachh-Bharat-Logo-PNG.png' className='w-28 mx-auto' />
                             </div>
                         </div> */}
                     </div>
                 </div>
             </div>

         </>
             
        )
    }
}
export default ComponentToPrint