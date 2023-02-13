//////////////////////////////////////////////////////////////////////////////////////
//    Author - Talib Hussain
//    Version - 1.0
//    Date - 14 july 2022
//    Revision - 1
//    Project - JUIDCO
//    Component  - PropertySafInbox (closed)
//    DESCRIPTION - PropertySafInbox Component
//////////////////////////////////////////////////////////////////////////////////////
import { useState } from "react";
import { useQuery } from "react-query";
import axios from "axios";
import { format } from "date-fns";
import ListTableAdvert from "../../../../Compnents/ListTableAdvert";
import AdvertisementApiList from "../../../../Compnents/AdvertisementApiList";
import ApiHeader from "../../../../Compnents/ApiHeader";
import Modal from 'react-modal';
import HoardingApplicationFullDetail from "./HoardingApplicationFullDetail";
import Loader from "../../Loader";
import AgencyNotification from "../AgencyNotification";
import BackToAgency from "../../BackToAgency";




const customStyles = {
    content: {
        top: '50%',
        left: '50%',
        right: 'auto',
        bottom: 'auto',
        marginRight: '-50%',
        transform: 'translate(-50%, -50%)',
        backgroundColor: 'white',
        border: 'none'
    },
};
Modal.setAppElement('#root');

function ViewAllHoardingRejectedList(props) {
    const [tableState, setTableState] = useState(false);
    const [listStatus, setlistStatus] = useState(false);
    const { api_getHoardingRejectedApplicationList } = AdvertisementApiList()
    const [applicationDetail, setapplicationDetail] = useState()

    const [applicationType, setapplicationType] = useState()
    const [applicationNo, setapplicationNo] = useState()
    const [show, setshow] = useState(false)
    const [modalIsOpen, setIsOpen] = useState(false);
    const openModal = () => setIsOpen(true)
    const closeModal = () => setIsOpen(false)
    const afterOpenModal = () => { }

    const [documentList, setdocumentList] = useState()

    const showLoader = (val) => {
        setshow(val);
    }



    const COLUMNS = [
        {
            Header: "#",
            // accessor: 'ward_no'
            Cell: ({ row }) => <div className="pr-2">{row.index + 1}</div>,
        },
        {
            Header: "Application No",
            accessor: "application_no",
        },
        {
            Header: "Apply Date.",
            accessor: "application_date",
        },
        {
            Header: "Apply From",
            accessor: "account_no",
        },
        {
            Header: "Firm Name",
            accessor: "bank_name",
        },
        {
            Header: "Action",
            accessor: "id",
            Cell: ({ cell }) => (
                <button
                    onClick={() => modalAction(cell.row.values.id, "Active")}
                    className="bg-indigo-500 px-3 py-1 rounded leading-5 shadow-lg hover:shadow-xl hover:bg-indigo-700 
                hover:text-white text-white"
                >
                    View
                </button>
            ),
        },
    ];

    const modalAction = (applicationId, applicationType) => {
        console.log("..............application id..............", applicationId)
        console.log("..............application type..............", applicationType)
        setapplicationNo(applicationId)
        setapplicationType(applicationType)
        openModal()
    }


    const onSuccess = (data) => {
        console.log("after fetching inbox list ....", data?.data?.data?.data);
        {
            data?.data?.data?.data?.length > 0 && setTableState(true);
        }
    };

    const { isLoading, data, isError, error } = useQuery(
        "safinboxList",
        () => {
            return axios.post(api_getHoardingRejectedApplicationList, {}, ApiHeader());
        },
        {
            onSuccess,
            refetchOnWindowFocus: false,
            refetchOnReconnect: false,
        }
    );
    return (
        <>
            <div className="">
                <BackToAgency />
            </div>
            <div className="grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 container mx-auto pt-4 ">
                <div className="col-span-8">
                    <div className="flex flex-row bg-white mb-4 rounded leading-5">
                        <img src="https://cdn-icons-png.flaticon.com/512/3288/3288006.png" className="h-10 ml-3 mt-2" />
                        <h1 className="text-xl text-gray-700 font-semibold p-4">HOARDING REJECTED APPLICATIONS</h1>
                    </div>
                    <Loader show={show} />
                    {isError && <ErrorPage />}
                    {!isLoading && !isError && tableState && (
                        <ListTableAdvert
                            assessmentType={false}
                            columns={COLUMNS}
                            dataList={data?.data?.data?.data}
                        />
                    )}
                    {!isLoading && !tableState && (
                        <div className="text-center h-56 mt-6">
                            <img
                                src="https://cdn-icons-png.flaticon.com/512/7466/7466140.png"
                                className="h-36 w-36 mx-auto"
                            />{" "}
                            <span>No data found...</span>{" "}
                        </div>
                    )}
                </div>
                <div className="col-span-4 bg-white">
                    <AgencyNotification />
                </div>

            </div>


            <Modal
                isOpen={modalIsOpen}
                onAfterOpen={afterOpenModal}
                onRequestClose={closeModal}
                style={customStyles}
                contentLabel="Example Modal"
            >
                <div class=" rounded-lg shadow-xl border-2 border-gray-50 mx-auto px-0 " style={{ 'width': '80vw', 'height': '100%' }}>
                    <HoardingApplicationFullDetail data={applicationNo} applicationType={applicationType} showLoader={showLoader} closeModal={closeModal} />
                </div>
            </Modal>

        </>
    );
}

export default ViewAllHoardingRejectedList;