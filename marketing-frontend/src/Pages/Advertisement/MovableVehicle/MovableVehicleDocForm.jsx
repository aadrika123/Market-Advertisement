
import { useFormik } from 'formik'
import React, { useState } from 'react'
import SelfAdvrtInformationScreen from '../SelfAdvertisement/SelfAdvrtInformationScreen'


function MovableVehicleDocForm(props) {

  let labelStyle = " text-sm text-gray-600"
  let inputStyle = "border shadow-md px-1.5 py-1 rounded-lg w-48"

  const [adharDocFile, setadharDocFile] = useState()
  const [adharDocFilePreview, setadharDocFilePreview] = useState()

  const [tradeLicenseDocFile, settradeLicenseDocFile] = useState()
  const [tradeLicenseDocFilePreview, settradeLicenseDocFilePreview] = useState()

  const [vehiclePhotoDocFile, setvehiclePhotoDocFile] = useState()
  const [vehiclePhotoDocFilePreview, setvehiclePhotoDocFilePreview] = useState()

  const [ownerBookDocFile, setownerBookDocFile] = useState()
  const [ownerBookDocFilePreview, setownerBookDocFilePreview] = useState()

  const [gstDocPhotoDocFile, setgstDocPhotoDocFile] = useState()
  const [gstDocPhotoDocFilePreview, setgstDocPhotoDocFilePreview] = useState()

  const [drivingLicenseDocFile, setdrivingLicenseDocFile] = useState()
  const [drivingLicenseDocFilePreview, setdrivingLicenseDocFilePreview] = useState()

  const [insurancePhotoDocFile, setinsurancePhotoDocFile] = useState()
  const [insurancePhotoFilePreview, setinsurancePhotoFilePreview] = useState()


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
    vehiclePhoto: vehiclePhotoDocFile,
    ownerBook: ownerBookDocFile,
    drivingLicense: gstDocPhotoDocFile,
    insurancePhoto: drivingLicenseDocFile,
    gstNoPhoto: insurancePhotoDocFile,

  }

  const formik = useFormik({
    initialValues: {
      aadharDoc: '',
      tradeLicenseDoc: '',
      vehiclePhoto: '',
      ownerBook: '',
      drivingLicense: '',
      insurancePhoto: '',
      gstNoPhoto: '',

    },
    onSubmit: values => {
      alert(JSON.stringify(values, null, 2));
      console.log("movable vehicle", values)
      props.collectFormDataFun('movableVehicleDoc', imagePath)
      props?.nextFun(2)

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
    if (name == 'vehiclePhoto') {
      let file = e.target.files[0]
      setvehiclePhotoDocFile(e.target.files[0])
      const reader = new FileReader()
      reader.onloadend = () => {
        setvehiclePhotoDocFilePreview(reader.result)
      }
      reader.readAsDataURL(file)
    }
    if (name == 'ownerBook') {
      let file = e.target.files[0]
      setownerBookDocFile(e.target.files[0])
      const reader = new FileReader()
      reader.onloadend = () => {
        setownerBookDocFilePreview(reader.result)
      }
      reader.readAsDataURL(file)
    }
    if (name == 'drivingLicense') {
      let file = e.target.files[0]
      setdrivingLicenseDocFile(e.target.files[0])
      const reader = new FileReader()
      reader.onloadend = () => {
        setdrivingLicenseDocFilePreview(reader.result)
      }
      reader.readAsDataURL(file)
    }
    if (name == 'insurancePhoto') {
      let file = e.target.files[0]
      setinsurancePhotoDocFile(e.target.files[0])
      const reader = new FileReader()
      reader.onloadend = () => {
        setinsurancePhotoFilePreview(reader.result)
      }
      reader.readAsDataURL(file)
    }
    if (name == 'gstNoPhoto') {
      let file = e.target.files[0]
      setgstDocPhotoDocFile(e.target.files[0])
      const reader = new FileReader()
      reader.onloadend = () => {
        setgstDocPhotoDocFilePreview(reader.result)
      }
      reader.readAsDataURL(file)
    }

  }

  return (
    <>
      <div className='-mt-[122rem] md:-mt-[43rem] lg:-mt-[43rem]'>
        <form onSubmit={formik.handleSubmit} onChange={handleChange} encType="multipart/form-data">
          <div className=' grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 container  mx-auto pb-8 p-2 mt-3'>
            <div className='col-span-8 p-1 border border-dashed border-violet-800'>
              <div className="p-1">
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

                  {/* Vehicle Photograph*/}
                  <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-1 ">
                    <div className='px-1'>
                      <div className="flex items-center">
                        <div className="mr-2  p-2">
                          <img src='https://cdn-icons-png.flaticon.com/512/4725/4725970.png' alt="doc" className='w-6 opacity-75' />
                        </div>
                        <span className={`${labelStyle}`}>Vehicle Photograph</span>
                      </div>
                    </div>
                    <div className='px-1'>
                      <div className="flex items-center justify-center font-semibold text-sm">
                        <div className="form-group col-span-4 md:col-span-1 md:px-0">
                          <input {...formik.getFieldProps('vehiclePhoto')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                          {/* <span className="text-red-600 absolute text-xs">{formik.touched.holdingNoDoc && formik.errors.holdingNoDoc ? formik.errors.holdingNoDoc : null}</span> */}
                        </div>
                      </div>
                    </div>
                    <div className='px-1'>
                      <div className="flex items-center justify-center font-semibold text-sm">
                        {vehiclePhotoDocFile == null || vehiclePhotoDocFile == undefined || vehiclePhotoDocFile == '' ? <img src='https://cdn-icons-png.flaticon.com/512/4194/4194756.png' alt="Preview Image" className={`${labelStyle} w-8`} /> :
                          <>
                            {vehiclePhotoDocFile?.name?.split('.').pop() == "pdf" && <img src='https://cdn-icons-png.flaticon.com/512/3997/3997593.png' alt="Preview Image" className={`${labelStyle} w-8`} />}
                            {vehiclePhotoDocFile?.name?.split('.').pop() == "jpg" && <img src={vehiclePhotoDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                            {vehiclePhotoDocFile?.name?.split('.').pop() == "png" && <img src={vehiclePhotoDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                          </>
                        }

                      </div>
                    </div>

                  </div>

                  {/* Owner Book*/}
                  <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-1 ">
                    <div className='px-1'>
                      <div className="flex items-center">
                        <div className="mr-2  p-2">
                          <img src='https://cdn-icons-png.flaticon.com/512/4725/4725970.png' alt="doc" className='w-6 opacity-75' />
                        </div>
                        <span className={`${labelStyle}`}>Owner Book</span>
                      </div>
                    </div>
                    <div className='px-1'>
                      <div className="flex items-center justify-center font-semibold text-sm">
                        <div className="form-group col-span-4 md:col-span-1 md:px-0">
                          <input {...formik.getFieldProps('ownerBook')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                          {/* <span className="text-red-600 absolute text-xs">{formik.touched.photoWithGps && formik.errors.photoWithGps ? formik.errors.photoWithGps : null}</span> */}
                        </div>
                      </div>
                    </div>
                    <div className='px-1'>
                      <div className="flex items-center justify-center font-semibold text-sm">
                        {ownerBookDocFile == null || ownerBookDocFile == undefined || ownerBookDocFile == '' ? <img src='https://cdn-icons-png.flaticon.com/512/4194/4194756.png' alt="Preview Image" className={`${labelStyle} w-8`} /> :
                          <>
                            {ownerBookDocFile?.name?.split('.').pop() == "pdf" && <img src='https://cdn-icons-png.flaticon.com/512/3997/3997593.png' alt="Preview Image" className={`${labelStyle} w-8`} />}
                            {ownerBookDocFile?.name?.split('.').pop() == "jpg" && <img src={ownerBookDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                            {ownerBookDocFile?.name?.split('.').pop() == "png" && <img src={ownerBookDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                          </>
                        }
                        {/* <img src={photoWithGpsDocFilePreview} alt="Preview Image" className={`${labelStyle}`} /> */}
                      </div>
                    </div>

                  </div>

                  {/*  GST Document*/}
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
                          <input {...formik.getFieldProps('gstNoPhoto')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                          {/* <span className="text-red-600 absolute text-xs">{formik.touched.gstDocPhoto && formik.errors.gstDocPhoto ? formik.errors.gstDocPhoto : null}</span> */}
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
                        {/* <img src={gstDocPhotoDocFilePreview} alt="Preview Image" className={`${labelStyle}`} /> */}
                      </div>
                    </div>

                  </div>

                  {/*  Driving License*/}
                  <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-1 ">
                    <div className='px-1'>
                      <div className="flex items-center">
                        <div className="mr-2  p-2">
                          <img src='https://cdn-icons-png.flaticon.com/512/4725/4725970.png' alt="doc" className='w-6 opacity-75' />
                        </div>
                        <span className={`${labelStyle}`}> Driving License</span>
                      </div>
                    </div>
                    <div className='px-1'>
                      <div className="flex items-center justify-center font-semibold text-sm">
                        <div className="form-group col-span-4 md:col-span-1 md:px-0">
                          <input {...formik.getFieldProps('drivingLicense')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                          {/* <span className="text-red-600 absolute text-xs">{formik.touched.proceedingPhoto1 && formik.errors.proceedingPhoto1 ? formik.errors.proceedingPhoto1 : null}</span> */}
                        </div>
                      </div>
                    </div>
                    <div className='px-1'>
                      <div className="flex items-center justify-center font-semibold text-sm">
                        {drivingLicenseDocFile == null || drivingLicenseDocFile == undefined || drivingLicenseDocFile == '' ? <img src='https://cdn-icons-png.flaticon.com/512/4194/4194756.png' alt="Preview Image" className={`${labelStyle} w-8`} /> :
                          <>
                            {drivingLicenseDocFile?.name?.split('.').pop() == "pdf" && <img src='https://cdn-icons-png.flaticon.com/512/3997/3997593.png' alt="Preview Image" className={`${labelStyle} w-8`} />}
                            {drivingLicenseDocFile?.name?.split('.').pop() == "jpg" && <img src={drivingLicenseDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                            {drivingLicenseDocFile?.name?.split('.').pop() == "png" && <img src={drivingLicenseDocFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                          </>
                        }

                      </div>
                    </div>

                  </div>

                  {/* Insurance Photo*/}
                  <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-1 ">
                    <div className='px-1'>
                      <div className="flex items-center">
                        <div className="mr-2  p-2">
                          <img src='https://cdn-icons-png.flaticon.com/512/4725/4725970.png' alt="doc" className='w-6 opacity-75' />
                        </div>
                        <span className={`${labelStyle}`}>Insurance Photo</span>
                      </div>
                    </div>
                    <div className='px-1'>
                      <div className="flex items-center justify-center font-semibold text-sm">
                        <div className="form-group col-span-4 md:col-span-1 md:px-0">
                          <input {...formik.getFieldProps('insurancePhoto')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                          {/* <span className="text-red-600 absolute text-xs">{formik.touched.proceedingPhoto2 && formik.errors.proceedingPhoto2 ? formik.errors.proceedingPhoto2 : null}</span> */}
                        </div>
                      </div>
                    </div>
                    <div className='px-1'>
                      <div className="flex items-center justify-center font-semibold text-sm">
                        {insurancePhotoDocFile == null || insurancePhotoDocFile == undefined || insurancePhotoDocFile == '' ? <img src='https://cdn-icons-png.flaticon.com/512/4194/4194756.png' alt="Preview Image" className={`${labelStyle} w-8`} /> :
                          <>
                            {insurancePhotoDocFile?.name?.split('.').pop() == "pdf" && <img src='https://cdn-icons-png.flaticon.com/512/3997/3997593.png' alt="Preview Image" className={`${labelStyle} w-8`} />}
                            {insurancePhotoDocFile?.name?.split('.').pop() == "jpg" && <img src={insurancePhotoFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                            {insurancePhotoDocFile?.name?.split('.').pop() == "png" && <img src={insurancePhotoFilePreview} alt="Preview Image" className={`${labelStyle} w-8`} />}
                          </>
                        }
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div className="grid grid-cols-12 w-full p-3">
                <div className='md:pl-0 col-span-6'>
                  <button type="button" class="py-2 px-4 text-xs inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-indigo-500 border border-indigo-500 hover:text-white hover:bg-indigo-600 hover:ring-0 hover:border-indigo-600 focus:bg-indigo-600 focus:border-indigo-600 focus:outline-none focus:ring-0" onClick={() => props.backFun(2)}>back</button>
                </div>

                <div className='col-span-6'>
                  <button type="submit" class="float-right text-xs py-2 px-4 inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-green-500 border border-green-500 hover:text-white hover:bg-green-600 hover:ring-0 hover:border-green-600 focus:bg-green-600 focus:border-green-600 focus:outline-none focus:ring-0">Save & Next</button>

                </div>
              </div>
            </div>
            <div className='col-span-4 hidden md:block lg:block'>
              <div className='-mt-20'>
                <SelfAdvrtInformationScreen />
              </div>
            </div>
          </div>
        </form>
      </div>
    </>
  )
}

export default MovableVehicleDocForm