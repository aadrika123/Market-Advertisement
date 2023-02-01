import React, { useEffect, useState } from "react";
import Modal from "react-modal";



import { useNavigate } from "react-router-dom";


const customStyles = {
    content: {
        top: "50%",
        left: "50%",
        right: "auto",
        bottom: "auto",
        marginRight: "-50%",
        transform: "translate(-50%, -50%)",
        background: "transparent",
        border: "none",
    },
};

function AdvertPaymentModal(props) {
    // Modal.setAppElement('#yourAppElement');
    const [modalIsOpen, setIsOpen] = React.useState(false);

    const navigate = useNavigate();
    useEffect(() => {
        if (props.openPaymentModal > 0) setIsOpen(true);
    }, [props.openPaymentModal]);



    function afterOpenModal() { }

    function closeModal() {
        setIsOpen(false);
        // props.refetchListOfRoles();
    }

    ///////////{*** PAYMENT METHOD ***}/////////
    const dreturn = (data) => {   // In (DATA) this function returns the Paymen Status, Message and Other Response data form Razorpay Server
        console.log('Payment Status =>', data)
        if (data?.status) {
            toast.success('Payment Success....', data)
            // return
            navigate(`/paymentScreen/${data?.data?.razorpay_payment_id}`)
        } else {
            toast.error('Payment failed....')
            navigate('/advertDashboard')
        }
    }




    const payNow = (e) => {
        let payApplicationId = e.target.id
        let payableAmount = e.target.value
        console.log("application id for payment process...", payApplicationId)
        console.log("payable amount for payment process...", payableAmount)
        console.log('loader clicked...')
        const orderIdPayload = {
            "id": payApplicationId,
            "amount": props?.safSubmitResponse?.data?.demand?.amounts?.payableAmount,
            "departmentId": 1,
            "workflowId": 4,
            "uldId": 2
        }
    }

    let token = window.localStorage.getItem('token')
    console.log('token at basic details is post method...', token)
    const header = {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
        }
    }
    // axios.post(propertyGenerateOrderId, orderIdPayload, header)  // This API will generate Order ID
    //     .then((res) => {
    //         console.log("Order Id Response ", res.data)
    //         if (res.data.status === true) {
    //             console.log("OrderId Generated True", res.data)
    //             setloaderStatus(false)

    //             RazorPaymentScreen(res.data.data, dreturn);  //Send Response Data as Object (amount, orderId, ulbId, departmentId, applicationId, workflowId, userId, name, email, contact) will call razorpay payment function to show payment popup                                      
    //             setTimeout(() => {
    //                 props.showLoader(false)
    //             }, 500)

    //         }
    //         else {
    //             setloaderStatus(false)

    //             props.showLoader(false)
    //         }
    //     })
    //     .catch((err) => {
    //         alert("Backend Server error. Unable to Generate Order Id");
    //         console.log("ERROR :-  Unable to Generate Order Id ", err)

    //         props.showLoader(false)
    //     })






    return (
        <div className="">

            <Modal
                isOpen={modalIsOpen}
                onAfterOpen={afterOpenModal}
                onRequestClose={closeModal}
                style={customStyles}
                contentLabel="Example Modal"
            >
                <div className="bg-white text-slate-50 shadow-2xl border border-violet-400 p-5 m-5 rounded-md"  >
                    <div className=''>
                        <div className='shadow-md shadow-violet-200 py-1 pl-3 bg-white border-gray-400 text-gray-600 flex'>Make Payment For<span className="font-bold text-md ml-4 ">APPLICATION NO. -   123456789 (SELF ADVERTISEMENT)</span></div>

                        <div className="md:flex block w-full h-56">
                            <div className='grid grid-cols-12 px-8 pt-3 leading-8 '>
                                <div className='md:col-span-6 col-span-12'>
                                    <div className='grid grid-cols-12'>
                                        <div className='col-span-6  text-gray-600'>
                                            <p>Entity Name</p>
                                            <p>Applied date</p>

                                        </div>
                                        <div className='col-span-6 text-gray-600'>
                                            <p>Applicant </p>
                                            <p>01/02/2023</p>
                                            {/* <p>{props?.OrderResponse?.applicationType}</p>
                                            <p>&emsp;{props?.OrderResponse?.licenceForYears} year <small>(s)</small></p>
                                            <p>{props?.OrderResponse?.applyDate == null ? JSON.stringify(new Date()).slice(1, 11) : props?.OrderResponse?.applyDate}</p> */}
                                        </div>
                                    </div>
                                </div>
                                <div className='md:col-span-6 col-span-12'>
                                    <div className='grid grid-cols-12'>
                                        <div className='col-span-6  text-gray-600'>
                                            <p>Entity Name</p>
                                            <p>Applied date</p>
                                        </div>
                                        <div className='col-span-6 text-gray-600 '>
                                            <p>Applicant </p>
                                            <p>01/02/2023</p>
                                        </div>
                                    </div>
                                </div>
                                <div className='md:col-span-6 col-span-12'>
                                    <div className='grid grid-cols-12'>
                                        <div className='col-span-6  text-gray-600'>
                                            <h1 className="font-bold text-lg ">Payable Amount</h1>

                                        </div>

                                    </div>
                                </div>
                                <div className='md:col-span-6 col-span-12'>
                                    <div className='grid grid-cols-12'>
                                        <div className='col-span-6  text-gray-600'>
                                            <h1 className="font-bold text-lg bg-violet-300 px-2 ">0.00</h1>

                                        </div>

                                    </div>
                                </div>

                                <div className='md:col-span-6 col-span-12'>
                                    <div className='grid grid-cols-12'>
                                        <div className='col-span-6  text-gray-600'>
                                            <button onClick={closeModal} className='mx-2 bg-red-600 hover:bg-red-700 transition duration-200 hover:scale-105 font-normal text-white px-6 py-1 text-sm  rounded-sm shadow-xl'>Cancel</button>
                                            <button onClick={payNow} className='mx-2 bg-indigo-600 hover:bg-indigo-700 transition duration-200 hover:scale-105 font-normal text-white px-6 py-1 text-sm  rounded-sm shadow-xl'>Pay Now</button>
                                        </div>
                                    </div>
                                </div>
                                <div className='md:col-span-6 col-span-12'>
                                    <div className='grid grid-cols-12'>
                                        <div className='col-span-6  text-gray-600'>
                                           <img src='https://cdn-icons-png.flaticon.com/256/7057/7057515.png' className="h-16"/>

                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div className=''>
                                <div className="">

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </Modal>
        </div>
    );
}

// ReactDOM.render(<App />, appElement);

export default AdvertPaymentModal;
