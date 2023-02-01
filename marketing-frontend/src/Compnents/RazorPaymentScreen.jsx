import axios from "axios";


// const { verifyPaymentStatus } = CitizenApplyApiList();


let token = window.localStorage.getItem('token')
console.log('token at basic details is post method...', token)
const header = {
    headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
    }
}

export default async function RazorPaymentScreen(generatedData, dreturn) {



    let returnData
    var options = {
        key: "rzp_test_3MPOKRI8WOd54p",
        amount: generatedData.amount,
        currency: "INR",
        image: "http://192.168.0.16:822/RMCDMC/public/assets/img/logo1.png",
        name: "JUDCO Corp.",
        description: "This is used for Testing Purpose",
        order_id: generatedData.orderId,
        handler: async function (response) {
            callApiLog(response)  // This function send the data to direct database => backend will verify the data
            console.log("All response", response)
            console.log("Payment ID", response.razorpay_payment_id);
            returnData = 'data from handle: payment success'
            dreturn({ status: true, message: 'Payment Success', data: response })
        },

        prefill: {
            name: generatedData.name,
            email: generatedData.email,
            contact: generatedData.mobile
        },
        "modal": {
            "ondismiss": function (response) {
                console.log("Payment Cancel BY user", response);
            },
            "onfailed": function (response) {
                console.log("Payment Failed Response modal", response);
            }
        },
        notes: {
            ulbId: generatedData.ulbId || 0,
            departmentId: generatedData.departmentId || 0,
            applicationId: generatedData.applicationId || 0,
            workflowId: generatedData.workflowId || 0,
            userId: generatedData.userId || 0,
            name: generatedData.name || 0,
            email: generatedData.email || 0,
            contact: generatedData.mobile || 0,
        },
        theme: {
            color: "#3399cc"
        }
    };
    var pay = new window.Razorpay(options);

    pay.on('payment.failed', function (response) {
        console.log("Failed Response", response)
        callApiLogFailed(response)  // This functin called when payment got failed. and data log will saved in bacend => using api 2
        dreturn({ status: false, message: 'Payment Failed', data: response })
    });

    pay.open();

    return returnData
}


//API 2 - when payment success we will keep the log in backend
const callApiLog = (response) => {

    const sendPayload = {
        "razorpayOrderId": response.razorpay_order_id,
        "razorpayPaymentId": response.razorpay_payment_id,
        "razorpaySignature": response.razorpay_signature
    }

    axios.post(verifyPaymentStatus, sendPayload, header) /// This API Will save the data. When response come after payment Sucess -> Not Nessesary
        .then((res) => {
            console.log("2nd API Data saved ", res)
        })
        .catch((err) => {
            console.log("Error when inserting 2 api data ", err)
        })
}

//API 2 - when payment failed we will keep the log in backend
const callApiLogFailed = (response) => {

    const sendPayload = {
        "razorpayOrderId": response.error.metadata.order_id,
        "razorpayPaymentId": response.error.metadata.payment_id,
        "reason": response.error.reason,
        "source": response.error.source,
        "step": response.error.step,
        "code": response.error.code,
        "description": response.error.description,
    }

    axios.post(verifyPaymentStatus, sendPayload, header) /// This API Will save the data. When response come after payment FAILED -> Not Nessesary
        .then((res) => {
            console.log("2nd API Filed Data saved ", res)
        })
        .catch((err) => {
            console.log("Error when inserting 2 api Failed data ", err)
        })
    // }


    return RazorPaymentScreen
}