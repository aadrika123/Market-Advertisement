import React from 'react'

class ComponentToPrint extends React.Component {
    render() {
        <>
            <div>
                <div className='md:px-0 flex-1 '>
                    {/* <Link to='/water'>
                        <button type="button" className="pl-4 pr-6 py-1 bg-gray-400 text-white font-medium text-xs leading-tight uppercase rounded  hover:bg-gray-500 hover: focus: focus:outline-none focus:ring-0  active: transition duration-150 ease-in-out">back</button>
                    </Link> */}
                </div>
                <div className='md:px-0 flex-1 '>
                    <button onClick={() => window.print()} className="float-right pl-4 pr-6 py-1 bg-sky-400 text-white font-medium text-xs leading-tight uppercase rounded  hover:bg-amber-100 hover: focus: focus:outline-none focus:ring-0  active: transition duration-150 ease-in-out">
                        {/* <AiFillPrinter className='inline text-lg' /> */}
                        print
                    </button>
                </div>
            </div>
            <div id="printableArea" className=''>

                <div>
                    {/* <NonBlockingLoader show={show} /> */}

                    <div className='border-2 border-dashed border-gray-600  bg-white p-6 w-[250mm] h-auto ml-12 md:mx-auto lg:mx-auto  container  mt-12 pb-12'>
                        <div className='grid grid-col-1 md:grid-col-12 lg:grid-col-12 '>
                            <div className=''>
                                <img src='https://upload.wikimedia.org/wikipedia/commons/thumb/a/a9/Jharkhand_Rajakiya_Chihna.svg/1200px-Jharkhand_Rajakiya_Chihna.svg.png' className='h-20 mx-auto' />
                            </div>
                            <div className=''>
                                <img src='https://upload.wikimedia.org/wikipedia/commons/thumb/a/a9/Jharkhand_Rajakiya_Chihna.svg/1200px-Jharkhand_Rajakiya_Chihna.svg.png' alt="" className=' w-[22rem] h-[22rem]  absolute z-10 bg-transparent opacity-20 mt-[16rem] ml-[17rem]  rounded-full border' />
                            </div>
                        </div>

                        {/* rmc */}
                        <div className='grid grid-col-1 md:grid-col-12 lg:grid-col-12 p-3 '>
                            <div className=''>
                                <h1 className='font-bold text-4xl text-center '>RANCHI MUNICIPAL CORPORATION</h1>
                            </div>
                        </div>

                        {/* holding tax */}
                        <div className='grid grid-col-1 md:grid-col-12 lg:grid-col-12 p-2 '>
                            <div className='mx-auto'>
                                <h1 className='font-semibold text-2xl text-center text-gray-800 border border-gray-700 w-[24rem] uppercase'>APPROVAL FORM</h1>
                            </div>
                        </div>

                        {/* detail section 1 */}
                        <div>
                            <table className='w-full  p-2 mt-2'>
                                <tr className=''>
                                    <td className=' '>
                                        <div className='flex p-1 text-xl'>
                                            <h1 className='flex text-gray-900 font-sans '>No. :</h1>
                                            <h1 className='flex font-sans font-semibold  pl-2'></h1>
                                        </div>

                                    </td>
                                    <td className='float-right '>
                                        <div className='flex p-1 text-xl'>
                                            <h1 className='flex text-gray-900 font-sans '>Date :</h1>
                                            <h1 className='flex font-sans font-semibold pl-2 '></h1>
                                        </div>

                                    </td>
                                </tr>
                            </table>
                        </div>


                        {/* detail section 2 */}
                        <div>
                            <table className='w-full  p-2 mt-4'>
                                <tr className=''>
                                    <td className=' '>
                                        <div className='flex p-1 text-xl'>
                                            <h1 className='flex text-gray-900 font-sans '>To ,</h1>
                                            <h1 className='flex font-sans font-semibold  pl-2'>.....................</h1>
                                        </div>
                                    </td>

                                </tr>
                            </table>
                        </div>
                        {/* N.B online */}
                        <div className='grid grid-col-1 md:grid-col-12 xl:grid-col-12 p-2 mt-3'>
                            <div className=''>
                                <h1 className=' text-xl '>Please refer to your application No. ...........,dated .......... for registration of installation of Outdoor Media Device for Display of outdoor Advertisement.
                                </h1>
                            </div>
                        </div>

                        {/* holding tax details */}
                        <div className='grid grid-col-1 md:grid-col-12 xl:grid-col-12 p-2 -mt-1' >
                            <div className=''>
                                <h1 className='text-xl text-left '>Dear Sir,</h1>
                            </div>
                        </div>

                        {/* Table 1 */}
                        <div>
                            <h1 className='text-xl'>
                                This is with reference to your application regarding registration with Urban Local Bodies for installation of an Outdoor Media Device for display of outdoor advertisements.
                                <br />
                                <br />
                                It is to inform that following decision has been taken in consideration of your application:
                                <br />
                                1.  Your application for registrationis approved and unique identification number alloted to you is ........ Please use the same for all future correspondance with the Urban Local Bodies and for activating your account on the website of Urban Local Bodies.
                                <br />
                                <br />
                                2.  Your application for new Media/renewal is rejected on account of the following :<br />

                                a.   Incomplete application.<br />
                                b.   Incorrect information provided<br />
                                c.   Pending dues with Municipal Corporation.<br />
                                d.   Blacklisted Status not verified<br />
                                e.   Others ..............
                            </h1>
                            <br />
                            <h1 className='text-xl'>
                                Thanking You,<br />
                                Municipal Commissioner/Executive Officer/Special Officer,<br />
                                Urban Local Bodies,
                            </h1>
                            <br />
                            <br />
                            <br />
                            <br />
                            <h1 className='text-xl'>
                                Note :   In case of rejection of application you may apply fresh on satisfying the above mentioned conditions. <br />
                                Note :   This is a Typical format only and is subject to modification/amendment by the Urban Local Bodies from time to time .
                            </h1>
                        </div>


                        {/* Qr code*/}
                        {/* <div>
                            <table className='w-full mt-10 '>
                                <tr className=''>
                                    <td className=' '>
                                        <div className=''>
                                            <QrCode value={this.props?.qrValue} size='64' />
                                            <QrCodE url='https://upload.wikimedia.org/wikipedia/commons/thumb/a/a9/Jharkhand_Rajakiya_Chihna.svg/1200px-Jharkhand_Rajakiya_Chihna.svg.png' size='64' />
                                        </div>
                                        <div className='flex '>
                                            <h1 className='flex text-gray-900 font-sans text-xl'>For Details Please Visit : udhd.jharkhand.gov.in</h1>
                                        </div>
                                        <div className='flex '>
                                            <h1 className='flex text-gray-900 font-sans text-xl'>Or Call us at 1800 8904115 or 0651-3500700</h1>
                                        </div>
                                    </td>
                                    <td className='float-right mt-16'>
                                        <div className='flex '>
                                            <h1 className='flex text-gray-900 font-sans text-xl'>In Collaboration with</h1>
                                        </div>
                                        <div className='flex'>
                                            <h1 className='flex text-gray-900 font-sans text-xl'>Sri Publication & Stationers Pvt Ltd</h1>
                                        </div>

                                    </td>
                                </tr>
                            </table>
                        </div> */}

                        {/* computer generated text */}
                        <div className='grid grid-col-1 md:grid-col-12 lg:grid-col-12 p-2 mt-2'>
                            <div className=''>
                                <h1 className='font-semibold text-xl text-center '>**This is a computer-generated receipt and it does not require a physical signature.**</h1>
                            </div>
                        </div>

                        {/* swatch bharat logo */}
                        <div className='grid grid-col-1 md:grid-col-12 lg:grid-col-12 p-2  mt-8'>
                            <div className=''>
                                <img src='https://zeevector.com/wp-content/uploads/LOGO/Swachh-Bharat-Logo-PNG.png' className='w-36 mx-auto' />
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </>
    }
}

export default ComponentToPrint