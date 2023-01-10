import { useFormik } from 'formik'
import React, { useState } from 'react'
import SelfAdvrtInformationScreen from '../SelfAdvertisement/SelfAdvrtInformationScreen'

function PrivateLandDocForm(props) {

    let labelStyle = " text-sm text-gray-600"
    let inputStyle = "border shadow-md px-1.5 py-1 rounded-lg w-48"

    const [adharDocFile, setadharDocFile] = useState()
    const [adharDocFilePreview, setadharDocFilePreview] = useState()

    const [tradeLicenseDocFile, settradeLicenseDocFile] = useState()
    const [tradeLicenseDocFilePreview, settradeLicenseDocFilePreview] = useState()

    const [gpsMappedCameraDocFile, setgpsMappedCameraDocFile] = useState()
    const [gpsMappedCameraDocFilePreview, setgpsMappedCameraDocFilePreview] = useState()

    const [holdingNoDocFile, setholdingNoDocFile] = useState()
    const [holdingNoDocFilePreview, setholdingNoDocFilePreview] = useState()

    const [gstDocPhotoDocFile, setgstDocPhotoDocFile] = useState()
    const [gstDocPhotoDocFilePreview, setgstDocPhotoDocFilePreview] = useState()

    const [brandDisplayPermisssionDocFile, setbrandDisplayPermisssionDocFile] = useState()
    const [brandDisplayPermisssionDocFilePreview, setbrandDisplayPermisssionDocFilePreview] = useState()


    // const validationSchema = yup.object({
    //     aadharDoc: yup.mixed(),
    //     tradeLicenseDoc: yup.mixed(),
    //     vehiclePhoto: yup.mixed(),
    //     ownerBook: yup.mixed(),
    //     gstDocPhoto: yup.mixed(),
    //     drivingLicense: yup.mixed(),
    //     insurancePhoto: yup.mixed(),
    // })

    const imagePath = {
        aadharDoc: adharDocFile,
        tradeLicenseDoc: tradeLicenseDocFile,
        gpsMappedCamera: gpsMappedCameraDocFile,
        holdingNoDoc: holdingNoDocFile,
        gstNoDoc: gstDocPhotoDocFile,
        brandDisplayPermissionDoc: brandDisplayPermisssionDocFile,


    }

    const formik = useFormik({
        initialValues: {
            aadharDoc: '',
            tradeLicenseDoc: '',
            gpsMappedCamera: '',
            holdingNoDoc: '',
            gstNoDoc: '',
            brandDisplayPermissionDoc: '',

        },
        onSubmit: values => {
            // alert(JSON.stringify(values, null, 2));
            console.log("agency doc", values)
            props.collectFormDataFun('agencyDoc', imagePath)
            props?.nextFun(4)

        },
        // validationSchema
    });


    const handleChange = (e) => {
        let name = e.target.name
        if (name == 'aadharDoc') {
            let file = e.target.files[0]
            setadharDocFile(e.target.files[0])
            const reader = new FileReader()
            reader.onloadend = () => {
                setadharDocFilePreview(reader.result)
            }
            reader.readAsDataURL(file)
        }
        if (name == 'tradeLicenseDoc') {
            let file = e.target.files[0]
            settradeLicenseDocFile(e.target.files[0])
            const reader = new FileReader()
            reader.onloadend = () => {
                settradeLicenseDocFilePreview(reader.result)
            }
            reader.readAsDataURL(file)
        }
        if (name == 'gpsMappedCamera') {
            let file = e.target.files[0]
            setgpsMappedCameraDocFile(e.target.files[0])
            const reader = new FileReader()
            reader.onloadend = () => {
                setgpsMappedCameraDocFilePreview(reader.result)
            }
            reader.readAsDataURL(file)
        }
        if (name == 'holdingNoDoc') {
            let file = e.target.files[0]
            setholdingNoDocFile(e.target.files[0])
            const reader = new FileReader()
            reader.onloadend = () => {
                setholdingNoDocFilePreview(reader.result)
            }
            reader.readAsDataURL(file)
        }
        if (name == 'gstNoDoc') {
            let file = e.target.files[0]
            setgstDocPhotoDocFile(e.target.files[0])
            const reader = new FileReader()
            reader.onloadend = () => {
                setgstDocPhotoDocFilePreview(reader.result)
            }
            reader.readAsDataURL(file)
        }
        if (name == 'brandDisplayPermissionDoc') {
            let file = e.target.files[0]
            setbrandDisplayPermisssionDocFile(e.target.files[0])
            const reader = new FileReader()
            reader.onloadend = () => {
                setbrandDisplayPermisssionDocFilePreview(reader.result)
            }
            reader.readAsDataURL(file)
        }


    }
    return (
        <>
            <div >
                <form onSubmit={formik.handleSubmit} onChange={handleChange} encType="multipart/form-data">
                    <div className=''>
                        <div className=' grid grid-cols-2 md:grid-cols-12 lg:grid-cols-12 gap-4 container  mx-auto pb-8 p-2 '>
                            <div className='col-span-8 p-1 border border-dashed border-violet-800 -mt-[32rem]'>
                                <div className="p-1 ">
                                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-1 p-1 bg-white ">
                                        <div>
                                            <h1 className='text-left  text-lg ml-12 text-gray-600 font-sans font-mono font-semibold'>Document</h1>
                                        </div>
                                        <div>
                                            <h1 className='text-center text-lg ml-3 text-gray-600 font-sans font-mono font-semibold'>Upload</h1>
                                        </div>
                                        <div>
                                            <h1 className='text-center text-lg ml-4 text-gray-600 font-sans font-mono font-semibold'>Preview</h1>
                                        </div>
                                    </div>
                                    <div className='mt-2'>
                                        {/* adhar document */}
                                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-1 ">
                                            <div className='px-1'>
                                                <div className="flex items-center">
                                                    <div className="mr-2  p-2">
                                                        <img src='https://cdn-icons-png.flaticon.com/512/4725/4725970.png' alt="doc" className='w-6 opacity-75' />
                                                    </div>
                                                    <span className={`${labelStyle}`}>Aadhar Document</span>
                                                </div>

                                            </div>
                                            <div className='px-1'>
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                                        <input {...formik.getFieldProps('aadharDoc')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                                                        {/* <span className="text-red-600 absolute text-xs">{formik.touched.aadharDoc && formik.errors.aadharDoc ? formik.errors.aadharDoc : null}</span> */}
                                                    </div>
                                                </div>
                                            </div>
                                            <div className='px-1'>
                                                <div className="flex items-center justify-center font-semibold text-sm" onClick={() => openDocView()}>
                                                    {adharDocFile == null || adharDocFile == undefined || adharDocFile == '' ? <img src='https://cdn-icons-png.flaticon.com/512/4194/4194756.png' alt="Preview Image" className={`${labelStyle} w-8`} /> :
                                                        <>
                                                            {adharDocFile?.name?.split('.').pop() == "pdf" && <img src='https://cdn-icons-png.flaticon.com/512/3997/3997593.png' alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                            {adharDocFile?.name?.split('.').pop() == "jpg" && <img src={adharDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                            {adharDocFile?.name?.split('.').pop() == "png" && <img src={adharDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                        </>
                                                    }
                                                </div>
                                            </div>

                                        </div>

                                        {/* Trade License */}
                                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-1 ">
                                            <div className='px-1'>
                                                <div className="flex items-center">
                                                    <div className="mr-2  p-2">
                                                        <img src='https://cdn-icons-png.flaticon.com/512/4725/4725970.png' alt="doc" className='w-6 opacity-75' />
                                                    </div>
                                                    <span className={`${labelStyle}`}>Trade License</span>
                                                </div>

                                            </div>
                                            <div className='px-1'>
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                                        <input {...formik.getFieldProps('tradeLicenseDoc')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                                                        {/* <span className="text-red-600 absolute text-xs">{formik.touched.tradeLicenseDoc && formik.errors.tradeLicenseDoc ? formik.errors.tradeLicenseDoc : null}</span> */}
                                                    </div>
                                                </div>
                                            </div>
                                            <div className='px-1'>
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    {tradeLicenseDocFile == null || tradeLicenseDocFile == undefined || tradeLicenseDocFile == '' ? <img src='https://cdn-icons-png.flaticon.com/512/4194/4194756.png' alt="Preview Image" className={`${labelStyle} w-8`} /> :
                                                        <>
                                                            {tradeLicenseDocFile?.name?.split('.').pop() == "pdf" && <img src='https://cdn-icons-png.flaticon.com/512/3997/3997593.png' alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                            {tradeLicenseDocFile?.name?.split('.').pop() == "jpg" && <img src={tradeLicenseDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                            {tradeLicenseDocFile?.name?.split('.').pop() == "png" && <img src={tradeLicenseDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                        </>
                                                    }

                                                </div>
                                            </div>

                                        </div>

                                        {/* GPS Mapped Camera*/}
                                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-1 ">
                                            <div className='px-1'>
                                                <div className="flex items-center">
                                                    <div className="mr-2  p-2">
                                                        <img src='https://cdn-icons-png.flaticon.com/512/4725/4725970.png' alt="doc" className='w-6 opacity-75' />
                                                    </div>
                                                    <span className={`${labelStyle}`}>GPS Mapped Camera</span>
                                                </div>
                                            </div>
                                            <div className='px-1'>
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                                        <input {...formik.getFieldProps('gpsMappedCamera')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                                                        {/* <span className="text-red-600 absolute text-xs">{formik.touched.gpsMappedCamera && formik.errors.gpsMappedCamera ? formik.errors.gpsMappedCamera : null}</span> */}
                                                    </div>
                                                </div>
                                            </div>
                                            <div className='px-1'>
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    {gpsMappedCameraDocFile == null || gpsMappedCameraDocFile == undefined || gpsMappedCameraDocFile == '' ? <img src='https://cdn-icons-png.flaticon.com/512/4194/4194756.png' alt="Preview Image" className={`${labelStyle} w-8`} /> :
                                                        <>
                                                            {gpsMappedCameraDocFile?.name?.split('.').pop() == "pdf" && <img src='https://cdn-icons-png.flaticon.com/512/3997/3997593.png' alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                            {gpsMappedCameraDocFile?.name?.split('.').pop() == "jpg" && <img src={gpsMappedCameraDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                            {gpsMappedCameraDocFile?.name?.split('.').pop() == "png" && <img src={gpsMappedCameraDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                        </>
                                                    }

                                                </div>
                                            </div>

                                        </div>

                                        {/*  Holding No. Photograph*/}
                                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-1 ">
                                            <div className='px-1'>
                                                <div className="flex items-center">
                                                    <div className="mr-2  p-2">
                                                        <img src='https://cdn-icons-png.flaticon.com/512/4725/4725970.png' alt="doc" className='w-6 opacity-75' />
                                                    </div>
                                                    <span className={`${labelStyle}`}>Holding No. Photograph</span>
                                                </div>
                                            </div>
                                            <div className='px-1'>
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                                        <input {...formik.getFieldProps('holdingNoDoc')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                                                        {/* <span className="text-red-600 absolute text-xs">{formik.touched.holdingNoDoc && formik.errors.holdingNoDoc ? formik.errors.holdingNoDoc : null}</span> */}
                                                    </div>
                                                </div>
                                            </div>
                                            <div className='px-1'>
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    {holdingNoDocFile == null || holdingNoDocFile == undefined || holdingNoDocFile == '' ? <img src='https://cdn-icons-png.flaticon.com/512/4194/4194756.png' alt="Preview Image" className={`${labelStyle} w-8`} /> :
                                                        <>
                                                            {holdingNoDocFile?.name?.split('.').pop() == "pdf" && <img src='https://cdn-icons-png.flaticon.com/512/3997/3997593.png' alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                            {holdingNoDocFile?.name?.split('.').pop() == "jpg" && <img src={holdingNoDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                            {holdingNoDocFile?.name?.split('.').pop() == "png" && <img src={holdingNoDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                        </>
                                                    }
                                                </div>
                                            </div>

                                        </div>

                                        {/*   GST No. Photograph*/}
                                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-1 ">
                                            <div className='px-1'>
                                                <div className="flex items-center">
                                                    <div className="mr-2  p-2">
                                                        <img src='https://cdn-icons-png.flaticon.com/512/4725/4725970.png' alt="doc" className='w-6 opacity-75' />
                                                    </div>
                                                    <span className={`${labelStyle}`}>GST Document</span>
                                                </div>
                                            </div>
                                            <div className='px-1'>
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                                        <input {...formik.getFieldProps('gstNoDoc')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                                                        {/* <span className="text-red-600 absolute text-xs">{formik.touched.gstNoDoc && formik.errors.gstNoDoc ? formik.errors.gstNoDoc : null}</span> */}
                                                    </div>
                                                </div>
                                            </div>
                                            <div className='px-1'>
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    {gstDocPhotoDocFile == null || gstDocPhotoDocFile == undefined || gstDocPhotoDocFile == '' ? <img src='https://cdn-icons-png.flaticon.com/512/4194/4194756.png' alt="Preview Image" className={`${labelStyle} w-8`} /> :
                                                        <>
                                                            {gstDocPhotoDocFile?.name?.split('.').pop() == "pdf" && <img src='https://cdn-icons-png.flaticon.com/512/3997/3997593.png' alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                            {gstDocPhotoDocFile?.name?.split('.').pop() == "jpg" && <img src={gstDocPhotoDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                            {gstDocPhotoDocFile?.name?.split('.').pop() == "png" && <img src={gstDocPhotoDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                        </>
                                                    }
                                                </div>
                                            </div>

                                        </div>

                                        {/* Brand Display Permission*/}
                                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-1 ">
                                            <div className='px-1'>
                                                <div className="flex items-center">
                                                    <div className="mr-2  p-2">
                                                        <img src='https://cdn-icons-png.flaticon.com/512/4725/4725970.png' alt="doc" className='w-6 opacity-75' />
                                                    </div>
                                                    <span className={`${labelStyle}`}>Brand Display Permission</span>
                                                </div>
                                            </div>
                                            <div className='px-1'>
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    <div className="form-group col-span-4 md:col-span-1 md:px-0">
                                                        <input {...formik.getFieldProps('brandDisplayPermissionDoc')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                                                        {/* <span className="text-red-600 absolute text-xs">{formik.touched.brandDisplayPermissionDoc && formik.errors.brandDisplayPermissionDoc ? formik.errors.brandDisplayPermissionDoc : null}</span> */}
                                                    </div>
                                                </div>
                                            </div>
                                            <div className='px-1'>
                                                <div className="flex items-center justify-center font-semibold text-sm">
                                                    {brandDisplayPermisssionDocFile == null || brandDisplayPermisssionDocFile == undefined || brandDisplayPermisssionDocFile == '' ? <img src='https://cdn-icons-png.flaticon.com/512/4194/4194756.png' alt="Preview Image" className={`${labelStyle} w-8`} /> :
                                                        <>
                                                            {brandDisplayPermisssionDocFile?.name?.split('.').pop() == "pdf" && <img src='https://cdn-icons-png.flaticon.com/512/3997/3997593.png' alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                            {brandDisplayPermisssionDocFile?.name?.split('.').pop() == "jpg" && <img src={brandDisplayPermisssionDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                            {brandDisplayPermisssionDocFile?.name?.split('.').pop() == "png" && <img src={brandDisplayPermisssionDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                                                        </>
                                                    }
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className="grid grid-cols-12 w-full p-3">
                                    <div className='md:pl-0 col-span-6'>
                                        <button type="button" class="text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={() => props.backFun(3)}>back</button>

                                    </div>

                                    <div className='col-span-6'>
                                        <button type="submit" class="text-xs float-right py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-green-500 border border-green-500 hover:text-white hover:bg-green-600 hover:ring-0 hover:border-green-600 focus:bg-green-600 focus:border-green-600 focus:outline-none focus:ring-0">Save & Next</button>

                                    </div>
                                </div>
                            </div>
                            <div className='col-span-4 hidden md:block lg:block -mt-[32rem]'>
                                <div className='-mt-20'>
                                    <SelfAdvrtInformationScreen />
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </>
    )
}

export default PrivateLandDocForm