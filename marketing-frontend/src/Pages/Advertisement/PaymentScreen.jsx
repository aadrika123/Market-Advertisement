import { height, width } from '@mui/system'
import React from 'react'
import payBg from '../../assets/images/payBg.jpg'

function PaymentScreen() {
    return (
        <>

            <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 bg-green-100 border p-8 h-screen w-full '  style={{backgroundImage: `url(${payBg})`,backgroundBlendMode:'darken',backgroundRepeat:'no-repeat',backgroundSize:'cover',}}>
                {/* <h1 className='text-3xl font-semibold text-center'>PAYMENT SUCCESSFUL</h1> */}
                <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 bg-gray-50 w-[32rem] h-[32rem] container mx-auto  shadow-2xl z-50 opacity-100'>
                    <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1'>
                        {/* <img src='https://cdn-icons-png.flaticon.com/512/2169/2169862.png' className='h-36 mx-auto' /> */}
                    </div>
                    <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1'>
                        <img src='https://cdn-icons-png.flaticon.com/512/5610/5610944.png' className='h-20 mx-auto opacity-75' />
                    </div>
                    <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 '>
                        <h1 className='text-center text-2xl  text-green-500'>Payment Successful</h1>
                    </div>

                    <div className='grid grid-cols-2 md:grid-cols-1 lg:grid-cols-1 p-4 pt-8'>
                        <div className='flex px-4'>
                            <div className='flex-1'>
                                <h1 className='text-gray-500 font-semibold text-md'>Payment Mode</h1>
                            </div>
                            <div className='flex-1'>
                                <h1 className='float-right text-gray-500 font-semibold text-md'> Net Banking</h1>
                            </div>
                        </div>
                        <div className='flex px-4'>
                            <div className='flex-1'>
                                <h1 className='text-gray-500 font-semibold text-md'>Bank</h1>
                            </div>
                            <div className='flex-1'>
                                <h1 className='float-right text-gray-500 font-semibold text-md'>HDFC</h1>
                            </div>
                        </div>
                        <div className='flex px-4'>
                            <div className='flex-1'>
                                <h1 className='text-gray-500 font-semibold text-md'>Mobile</h1>
                            </div>
                            <div className='flex-1'>
                                <h1 className='float-right text-gray-500 font-semibold text-md'>0987654321</h1>
                            </div>
                        </div>
                        <div className='flex px-4'>
                            <div className='flex-1'>
                                <h1 className='text-gray-500 font-semibold text-md'>Email</h1>
                            </div>
                            <div className='flex-1'>
                                <h1 className='float-right text-gray-500 font-semibold text-md'>abc@gmail.com</h1>
                            </div>
                        </div>
                        <div className='flex px-4 pt-4'>
                            <div className='flex-1'>
                                <h1 className='text-gray-600 font-bold text-md'>Amount Paid</h1>
                            </div>
                            <div className='flex-1'>
                                <h1 className='float-right text-gray-600 font-bold text-md'>1000.00</h1>
                            </div>
                        </div>
                        <div className='flex px-4 pt-4'>
                            <div className='flex-1'>
                                <h1 className='text-gray-500 font-semibold text-md'>Transcation id</h1>
                            </div>
                            <div className='flex-1'>
                                <h1 className='float-right text-gray-500 font-semibold text-md'>123654789</h1>
                            </div>
                        </div>
                    </div>

                    <div className='grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 mb-4'>
                        <div className='flex space-x-4'>
                            <div className='flex-1 text-right'>
                                <button className='text-sm bg-indigo-500 text-white px-4 py-2 text-center'>
                                    PRINT
                                </button>
                            </div>
                            <div className='flex-1 text-left'>
                                <button className='text-sm bg-indigo-500 text-white px-4 py-2 text-center'>
                                    CLOSE
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



        </>
    )
}

export default PaymentScreen