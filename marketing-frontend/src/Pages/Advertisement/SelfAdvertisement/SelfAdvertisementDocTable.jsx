// import { Modal } from '@mui/material';
import Modal from 'react-modal';
import { useFormik } from 'formik';
import React, { useState } from 'react'



const customStyles = {
    content: {
        top: '50%',
        left: '50%',
        right: 'auto',
        bottom: 'auto',
        marginRight: '-50%',
        transform: 'translate(-50%, -50%)',
        backgroundColor: 'gray',
        border: 'none'
    },
};
Modal.setAppElement('#root');

function SelfAdvertisementDocTable(props) {


    let labelStyle = " text-xs  text-gray-600"
    let inputStyle = "border shadow-md px-1.5 py-1 rounded-lg w-48"

    const [imgPath, setimgPath] = useState()
    const [DocUrl, setDocUrl] = useState('')
    const [modalIsOpen, setIsOpen] = useState(false);


    const openModal = () => setIsOpen(true)
    const closeModal = () => setIsOpen(false)
    const afterOpenModal = () => { }


    const modalAction = (url) => {
        // alert(url)
        setDocUrl(url)
        openModal()
    }

    const initialValues = {
        id: props?.data?.id,
        image: "",
        relativeName: props?.data?.relative_name
    };

    const formik = useFormik({
        initialValues: initialValues,
        onSubmit: (values) => {
            console.log("values", values);
            setimgPath(values)
            props.collectAllDataFun(props.mykey, values);
        }
    });

    const handleOnChange = (e) => {
        let name = e.target.name;
        let value = e.target.value;
        let file = e.target.files[0]

        { name == "image" && handleFile(file) }
    };

    const handleFile = (file) => {
        formik.setFieldValue("image", file);
        const reader = new FileReader()
        reader.onloadend = () => {
            setDocUrl(reader.result)
        }
        reader.readAsDataURL(file)

        console.log("file checker ", DocUrl)
    }

    console.log("url", DocUrl)
    console.log("path", imgPath)
    return (
        <div>
            <form onSubmit={formik.handleSubmit}
                onChange={handleOnChange}
                encType="multipart/form-data">

                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 gap-1 ">
                    <div className='px-1'>
                        <div className="">
                            {/* <div className="mr-2  p-2">
                                <img src='https://cdn-icons-png.flaticon.com/512/4725/4725970.png' alt="doc" className='w-6 opacity-75' />
                            </div> */}
                            <span className={`${labelStyle}`} >{props?.data?.document_name}</span>
                        </div>

                    </div>
                    <div className='px-1'>
                        <div className="flex items-center justify-center font-semibold text-sm" >
                            {imgPath?.image?.name == null || imgPath?.image?.name == undefined || imgPath?.image?.name == '' ? <img src='https://cdn-icons-png.flaticon.com/512/4194/4194756.png' alt="Preview Image" className={`${labelStyle} w-8`} /> :
                                <>
                                    <div onClick={() => modalAction(DocUrl)}>
                                        {imgPath?.image?.name?.split('.').pop() == "pdf" && <img src='https://cdn-icons-png.flaticon.com/512/3997/3997593.png' alt="Preview Image" className={`${labelStyle} w-8`} />}
                                        {imgPath?.image?.name?.split('.').pop() == "jpg" && <img src="https://cdn-icons-png.flaticon.com/512/5719/5719824.png" alt="Preview Image" className={`${labelStyle} w-8`} />}
                                        {imgPath?.image?.name?.split('.').pop() == "jpeg" && <img src="https://cdn-icons-png.flaticon.com/512/5719/5719824.png" alt="Preview Image" className={`${labelStyle} w-8`} />}
                                        {imgPath?.image?.name?.split('.').pop() == "png" && <img src="https://cdn-icons-png.flaticon.com/512/5719/5719894.png" alt="Preview Image" className={`${labelStyle} w-8`} />}
                                    </div>

                                </>
                            }
                        </div>
                    </div>
                    <div className='px-1'>
                        <div className="flex items-center justify-center font-semibold text-sm">
                            <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                <input type='file' name='image' className="form-control block w-full px-3 py-1.5 mb-3 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory onChange={formik.handleChange} />
                                {/* onChange={(e) => setDocPath(e.target.files)}  */}

                            </div>
                        </div>
                    </div>
                    <div className='px-1'>
                        <div className="flex items-center justify-center font-semibold text-sm">
                            <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                {imgPath?.image == null || imgPath?.image == undefined || imgPath?.image == '' ?
                                    < button type='submit' className='py-0 px-2 text-[0.7rem] inline-block text-center  rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0' >
                                        <span className='text-xs'>upload</span>
                                    </button> :
                                    <>
                                        <p className='text-xs text-teal-500 font-bold font-BreeSerif'> Uploaded...</p>
                                        < button type='submit' className='py-0 px-2 text-[0.7rem] inline-block text-center  rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0' >
                                            <span className='text-xs'>re-upload</span>
                                        </button>
                                    </>
                                }
                            </div>
                        </div>
                    </div>


                </div>
            </form >

            <Modal
                isOpen={modalIsOpen}
                onAfterOpen={afterOpenModal}
                onRequestClose={closeModal}
                style={customStyles}
                contentLabel="Example Modal"
            >

                <div class=" rounded-lg shadow-xl border-2 border-gray-50 mx-auto px-0" style={{ 'width': '40vw', 'height': '80vh' }}>
                    <iframe className='w-full h-full' src={DocUrl} frameborder="1">
                        {/* <object data={DocUrl} width="200" height="200"></object> */}
                        <img className='' src={DocUrl} />
                    </iframe>
                </div>

            </Modal>
            {/* <iframe className='w-full h-full' src={DocUrl} frameborder="0">
                <object data={DocUrl} width="300" height="300"></object>
                <img src={DocUrl} />
            </iframe> */}
        </div >


    )
}

export default SelfAdvertisementDocTable