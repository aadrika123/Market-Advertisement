//////////////////////////////////////////////////////////////////////////////////////
//    Author - Dipu Singh
//    Version - 1.0
//    Date - 06 Dec 2022
//    Revision - 1
//    Project - JUIDCO
//    Component  - 
//    DESCRIPTION - 
//////////////////////////////////////////////////////////////////////////////////////

import React, { useEffect, useState } from "react";
import Modal from "react-modal";
import { GrClose } from "react-icons/gr";
import axios from "axios";
// import { Document, Page, pdfjs } from 'react-pdf';

const customStyles = {
    content: {
        top: '50%',
        left: '50%',
        right: 'auto',
        bottom: 'auto',
        marginRight: '-50%',
        transform: 'translate(-50%, -50%)',
        backgroundColor: 'transparent',
        border: 'none'
    },
};
Modal.setAppElement('#root');

function ViewUploadedDoc(props) {
    // Modal.setAppElement('#yourAppElement');
    const [modalIsOpen, setIsOpen] = React.useState(false);


    useEffect(() => {
        if (props.openDocviewModal > 0) setIsOpen(true);
    }, [props.openDocviewModal]);

    function afterOpenModal() { }

    function closeModal() {
        setIsOpen(false);
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
                <div style={{ 'width': '71.5vw', 'height': '70vh' }}>
                    <div className="bg-sky-200 shadow-2xl border border-sky-200 p-3 rounded-md z-50" >
                        <div onClick={closeModal} className="absolute cursor-pointer bg-green-100 hover:bg-gray-300 p-1 rounded-md"><GrClose /> </div>
                        <div className='py-2 pl-3 font-semibold text-center text-2xl text-gray-800'> Document For {'{docName}'}</div>
                        <div className="scroll-auto">

                            {props?.documentUrl?.split('.').pop() == "pdf" ?
                                <iframe style={{ 'width': '70vw', 'height': '50vh' }} src={props?.documentUrl} frameborder="0"></iframe>
                                : <div className="flex justify-center">
                                    < img src={props?.documentUrl} className="rounded-md" alt="Uploaded Image" srcset="" />
                                </div>
                            }
                        </div>
                        {props?.documentUrl}
                        <div className='flex justify-center'>
                            <button onClick={closeModal} className='mx-2 bg-red-600 hover:bg-red-700 transition duration-200 hover:scale-105 font-normal text-white px-6 py-1 text-sm rounded-sm shadow-xl'>Close</button>
                        </div>
                    </div>


                </div>
            </Modal>
        </div>
    );
}

// ReactDOM.render(<App />, appElement);

export default ViewUploadedDoc;
