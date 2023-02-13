import axios from "axios";
import React, { useEffect, useState } from "react";
import Modal from "react-modal";
import RazorPaymentScreen from '../../Compnents/RazorPaymentScreen'


import { useNavigate } from "react-router-dom";
import AdvertisementApiList from "../../Compnents/AdvertisementApiList";
import ApiHeader from "../../Compnents/ApiHeader";


const customStyles = {
    content: {
        top: "50%",
        left: "50%",
        right: "auto",
        bottom: "auto",
        marginRight: "-50%",
        transform: "translate(-50%, -50%)",
        background: "",
        border: "none",
    },
};

function AdvertPaymentModal(props) {
    // Modal.setAppElement('#yourAppElement');
    const [modalIsOpen, setIsOpen] = React.useState(false);

    const { api_getOrderIdForPayment } = AdvertisementApiList()
    const [orderId, setorderId] = useState()
    const navigate = useNavigate();
    useEffect(() => {
        if (props.openPaymentModal > 0) setIsOpen(true);
    }, [props.openPaymentModal]);

    console.log("payment card details", props?.applicationDetails?.id)

    function afterOpenModal() { }

    function closeModal() {
        setIsOpen(false);
        // props.refetchListOfRoles();
    }

    const notify = (toastData, type) => {
        toast.dismiss();
        if (type == 'success') {
            toast.success(toastData)
        }
        if (type == 'error') {
            toast.error(toastData)
        }
    };


    ///////////{*** PAYMENT METHOD ***}/////////
    const dreturn = (data) => {   // In (DATA) this function returns the Paymen Status, Message and Other Response data form Razorpay Server
        console.log('Payment Status =>', data)
        if (data?.status === true) {
            navigate(`/paymentScreen`)
            notify("Payment Successfull", "success")
        } else {
            toast.error('Payment failed....')
            notify("Payment Failed", "error")
            navigate('/advertDashboard')
        }
    }

    const payNow = (e) => {
        props.showLoader(true)
        console.log("payment id on click pay", e.target.id)
        let applicationId = e.target.id
        const requestBody = {
            id: applicationId
        }
        axios.post(`${api_getOrderIdForPayment}`, requestBody, ApiHeader())
            .then(function (response) {
                console.log('generate order id', response.data)
                if (response.data.status == true) {
                    console.log("OrderId Generated True", response.data)
                    RazorPaymentScreen(response.data.data, dreturn)  //Send Response Data as Object (amount, orderId, ulbId, departmentId, applicationId, workflowId, userId, name, email, contact) will call razorpay payment function to show payment popup     
                }
                else {

                }
                setorderId(response)
                setTimeout(() => {
                    props.showLoader(false);
                }, 500);
            })
            .catch(function (error) {
                alert("Backend Server error. Unable to Generate Order Id");
                console.log("ERROR :-  Unable to Generate Order Id ", err)

                setTimeout(() => {
                    props.showLoader(false);
                }, 500);

            })
    }

    return (
        <div className="">

            <Modal
                isOpen={modalIsOpen}
                onAfterOpen={afterOpenModal}
                onRequestClose={closeModal}
                style={customStyles}
                contentLabel="Example Modal"
            >
                <div className="bg-violet-200 text-slate-50 drop-shadow-2xl border border-violet-500 p-5 m-5 rounded-md"  >
                    <div className=''>
                        <div className='shadow-md shadow-violet-300 py-1 pl-3 bg-white border-gray-400 text-gray-600 flex'>Make Payment For<span className="font-bold text-md ml-4 uppercase ">{props?.applicationDetails?.type} </span></div>

                        <div className="md:flex block w-full h-56">
                            <div className='grid grid-cols-12 px-8 pt-3 leading-8 '>
                                <div className='md:col-span-6 col-span-12'>
                                    <div className='grid grid-cols-12'>
                                        <div className='col-span-6  text-gray-600'>
                                            <p>Application No. -</p>
                                            <p>Applied date -</p>

                                        </div>
                                        <div className='col-span-6 text-gray-500 font-bold'>
                                            <p>{props?.applicationDetails?.application_no} </p>
                                            <p>{props?.applicationDetails?.application_date}</p>
                                            {/* <p>{props?.OrderResponse?.applicationType}</p>
                                            <p>&emsp;{props?.OrderResponse?.licenceForYears} year <small>(s)</small></p>
                                            <p>{props?.OrderResponse?.applyDate == null ? JSON.stringify(new Date()).slice(1, 11) : props?.OrderResponse?.applyDate}</p> */}
                                        </div>
                                    </div>
                                </div>
                                <div className='md:col-span-6 col-span-12'>
                                    <div className='grid grid-cols-12'>
                                        <div className='col-span-6  text-gray-600'>
                                            <p>Applicant Name -</p>
                                            <p>Entity Name -</p>
                                        </div>
                                        <div className='col-span-6 text-gray-500 font-bold'>
                                            <p>{props?.applicationDetails?.applicant}  </p>
                                            <p>{props?.applicationDetails?.entity_name} </p>
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
                                            <h1 className="font-bold text-lg bg-violet-300 px-2 ">{props?.applicationDetails?.payment_amount}</h1>

                                        </div>

                                    </div>
                                </div>

                                <div className='md:col-span-6 col-span-12'>
                                    <div className='grid grid-cols-12'>
                                        <div className='col-span-6  text-gray-600'>
                                            <button onClick={closeModal} className='mx-2 bg-red-600 hover:bg-red-700 transition duration-200 hover:scale-105 font-normal text-white px-6 py-1 text-sm  rounded-sm shadow-xl'>Cancel</button>
                                            <button onClick={payNow} id={props?.applicationDetails?.id} className='mx-2 bg-indigo-600 hover:bg-indigo-700 transition duration-200 hover:scale-105 font-normal text-white px-6 py-1 text-sm  rounded-sm shadow-xl'>Pay Now</button>
                                        </div>
                                    </div>
                                </div>
                                <div className='md:col-span-6 col-span-12'>
                                    <div className='grid grid-cols-12'>
                                        <div className='col-span-6  text-gray-600'>
                                            <img src='https://cdn-icons-png.flaticon.com/256/7057/7057515.png' className="h-16" />

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
