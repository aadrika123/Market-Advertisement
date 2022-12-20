import React, { useEffect, useState } from "react";
import Modal from "react-modal";
import { GrFormClose } from "react-icons/gr";
import { HiCurrencyRupee } from "react-icons/hi";
import { FiUpload } from 'react-icons/fi';
import axios from "axios";


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

function DocUploadModal(props) {
    // Modal.setAppElement('#yourAppElement');
    const [modalIsOpen, setIsOpen] = React.useState(false);
    const [documentList, setDocumentList] = useState()
    const [ownerDocList, setOwnerDocList] = useState()
    const [docType, setDocType] = useState()


    const [docPath, setDocPath] = useState()


    useEffect(() => {
        if (props.openDocumentModal > 0) setIsOpen(true);
    }, [props.openDocumentModal]);

    function afterOpenModal() { }

    function closeModal() {
        setIsOpen(false);
        // props.refetchListOfRoles();
    }

    let token = window.localStorage.getItem('token')
    const header = {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
            enctype: 'multipart/form-data',
        }
    }

    const docUpload = (e) => {
        e.preventDefault();

        let formData = new FormData();

        formData.append('applicationId', props?.payloadData?.applicationId);  
        formData.append('docPath', docPath);  //doucment master id
        formData.append('docMstrId', docType);            //Btn name
        formData.append('docFor', props?.payloadData?.docName);            //File information
        // formData.append('owner_id', ownerId);   //ownerID


        console.log("Complate Payload", formData);

        axios.post(api_getDocList, formData, header)
            .then((res) => {
                console.log("Data Uploaded", res)
                closeModal()
                props.refetch()
            })
            .catch((err) => console.log("Expection...", err))

        // setFileSelected(e.target.files[0]);
    };

    // useEffect(() => {
    //     axios.post(api_getDocList, { "applicationId": props?.payloadData?.applicationId }, HEADER())
    //         .then((res) => {
    //             console.log("Llist of document to upload", res)
    //             if (res.data.status == true) {
    //                 setDocumentList(res.data.data.documentsList)
    //                 setOwnerDocList(res.data.data.ownersDocList)
    //             }
    //         })
    //         .catch((err) => console.log("Error in Document upload list", err))
    // }, [props?.payloadData?.applicationId])



    return (
        <div className="">
            <Modal
                isOpen={modalIsOpen}
                onAfterOpen={afterOpenModal}
                onRequestClose={closeModal}
                style={customStyles}
                contentLabel="Example Modal"
            >
                <form encType="multipart/form" onSubmit={docUpload} >
                    <div className="bg-white shadow-2xl border border-sky-200 p-5 m-2 rounded-md">
                        <div className="md:inline-block min-w-full overflow-hidden hidden">
                            <table className="min-w-full leading-normal border">
                                <thead className='bg-sky-100'>
                                    <tr className='font-semibold '>
                                        <th scope="col" className="px-5 py-2 border-b border-gray-200 text-gray-800  text-left text-sm uppercase">
                                            #
                                        </th>
                                        <th scope="col" className="px-5 py-2 border-b border-gray-200 text-gray-800  text-left text-sm uppercase">
                                            Document Name
                                        </th>
                                        <th scope="col" className="px-5 py-2 border-b border-gray-200 text-gray-800  text-left text-sm uppercase">
                                            Document Type
                                        </th>

                                        <th scope="col" className="px-5 py-2 border-b border-gray-200 text-gray-800  text-left text-sm uppercase">
                                            Upload
                                        </th>
                                        <th scope="col" className="px-5 py-2 border-b border-gray-200 text-gray-800  text-left text-sm uppercase">
                                            Submit
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {
                                        // documentList?.filter(e => e.docName == props?.payloadData?.docName).map((e, i = 1) => (
                                            <tr>
                                                <td className="px-5 py-2 border-b border-gray-200 bg-white text-sm">
                                                    <p className="text-gray-900 whitespace-no-wrap">
                                                        1
                                                    </p>
                                                </td>
                                                <td className="px-5 py-2 border-b border-gray-200 bg-white text-sm">
                                                    <p className="text-gray-900 whitespace-no-wrap">
                                                    Upload Fire Extinguishers Photograph <span className="text-red-500 font-semibold mx-1">*</span>
                                                    </p>
                                                </td>
                                                <td className="px-5 py-2 border-b border-gray-200 bg-white text-sm">
                                                    <p className="text-gray-900 whitespace-no-wrap">
                                                        <select onChange={(e) => setDocType(e.target.value)} className=' outline-blue-600 border border-gray-400 w-40'>
                                                            <option>Select Documet</option>
                                                            <option>Doc File</option>
                                                            {/* {
                                                                e?.docVal?.map((item) => (
                                                                    <option value={item.id}>{item.document_name}</option>
                                                                ))
                                                            } */}
                                                        </select>
                                                    </p>
                                                </td>

                                                <td className="px-5 border-b border-gray-200 bg-white text-sm">
                                                    <span className="relative inline-block ">
                                                        <span aria-hidden="true" className="absolute inset-0 "></span>
                                                        <span className="relative ">
                                                            <input
                                                                name="docPath"
                                                                type="file"
                                                                // accept="image/*"
                                                                style={{ display: 'none' }}
                                                                id="contained-button-file"
                                                                onChange={(e) => setDocPath(e.target.files[0])}
                                                            // onChange={() => importFile(e.docName)}
                                                            />
                                                            <label className='bg-blue-500 hover:bg-blue-600 hover:ring-1 hover:ring-blue-800 rounded-sm hover:rounded-md hover:shadow-2xl shadow-lg cursor-pointer flex pl-4 pr-5 py-2  text-white font-medium' htmlFor="contained-button-file"><FiUpload size={16} className='mt-0 ml-0 mr-2 font-medium' />Browse File</label>
                                                        </span>
                                                    </span>
                                                </td>
                                                <td className="px-5 py-2 border-b border-gray-200 bg-white text-sm">
                                                    <button type="submit" className="border bg-green-600 rounded-md text-white px-4 py-2">Submit</button>
                                                </td>
                                            </tr>
                                        // ))
                                    }

                                </tbody>
                            </table>
                            {/* <div className='my-5 flex justify-center'>
                            <div className='mx-2'><button onClick={closeModal} className='bg-red-400 hover:bg-red-500 px-5 py-2 shadow-xl rounded-md text-black text-base'>Cancel</button></div>
                            <div className='mx-2'><button className='bg-green-400 hover:bg-green-500 px-5 py-2 shadow-xl rounded-md text-black text-base float-right'>Submit</button></div>
                        </div> */}
                        </div>
                    </div>
                </form>
            </Modal>
        </div>
    );
}

// ReactDOM.render(<App />, appElement);

export default DocUploadModal;

