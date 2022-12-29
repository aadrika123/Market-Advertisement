
import { useFormik } from 'formik'
import React, { useState } from 'react'


function MovableVehicleDocForm(props) {

  let labelStyle = "mt-4 pl-1 text-sm text-gray-600"
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
      props?.nextFun(1)

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
      <form onSubmit={formik.handleSubmit} onChange={handleChange} encType="multipart/form-data">
        <div className="container mx-auto bg-white overflow-x-auto -mt-[54rem]">
          <div className=' grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 p-4 '>
            <div className='flex flex-row'>
              <img src='https://cdn-icons-png.flaticon.com/512/3039/3039527.png' className='h-6 mt-2 opacity-80' />
              <h1 className='text-2xl ml-2 text-gray-600 font-sans '>Upload Document</h1>
            </div>
            {/* <h1 className='text-sm ml-9 text-gray-400 font-sans'>You Can Get License To Advertise Your Business Name On Your Shop</h1> */}
          </div>
          <div className="min-w-screen min-h-screen  flex md:pl-4 p-4 bg-white font-sans overflow-x-auto">
            <div className="w-full lg:w-4/6 mx-auto">
              <div className="bg-gray-50 shadow-md rounded my-2">
                <table className="min-w-max w-full table-auto">
                  <thead>
                    <tr className="bg-blue-200 text-gray-600 uppercase text-sm leading-normal">
                      <th className="py-3 px-6 text-left cursor-pointer" onClick={() => notify('just testing the context data', 'info')}>Image Type</th>
                      <th className="py-3 px-6 text-left">Upload</th>
                      <th className="py-3 px-6 text-center">Preview</th>
                    </tr>
                  </thead>
                  <tbody className="text-gray-600 text-sm font-light bg-white">
                    {/* adhar document */}
                    <tr className="border-b border-gray-200 ">
                      <td className="py-3 px-6 text-left whitespace-nowrap">
                        <div className="flex items-center">
                          <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                            <img alt="rain" className='w-3' />
                          </div>
                          <span className="font-medium">Aadhar Document</span>
                        </div>
                      </td>
                      <td className="py-3 px-6">
                        <div className="flex items-center justify-center font-semibold text-sm">
                          <div className="form-group col-span-4 md:col-span-1 md:px-0">
                            <input {...formik.getFieldProps('aadharDoc')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                            {/* <span className="text-red-600 absolute text-xs">{formik.touched.aadharDoc && formik.errors.aadharDoc ? formik.errors.aadharDoc : null}</span> */}
                          </div>
                        </div>
                      </td>
                      <td className="py-3 px-6 text-center">
                        <div className="flex items-center justify-center font-semibold text-sm">
                          <img src={adharDocFilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                        </div>
                      </td>
                    </tr>
                    {/* Trade License */}
                    <tr className="border-b border-gray-200 ">
                      <td className="py-3 px-6 text-left whitespace-nowrap">
                        <div className="flex items-center">
                          <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                            <img alt="rain" className='w-3' />
                          </div>
                          <span className="font-medium">Trade License</span>
                        </div>
                      </td>
                      <td className="py-3 px-6">
                        <div className="flex items-center justify-center font-semibold text-sm">
                          <div className="form-group col-span-4 md:col-span-1 md:px-0">
                            <input {...formik.getFieldProps('tradeLicenseDoc')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                            {/* <span className="text-red-600 absolute text-xs">{formik.touched.tradeLicenseDoc && formik.errors.tradeLicenseDoc ? formik.errors.tradeLicenseDoc : null}</span> */}
                          </div>
                        </div>
                      </td>
                      <td className="py-3 px-6 text-center">
                        <div className="flex items-center justify-center font-semibold text-sm">
                          <img src={tradeLicenseDocFilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                        </div>
                      </td>
                    </tr>
                    {/* Vehicle Photograph*/}
                    <tr className="border-b border-gray-200 ">
                      <td className="py-3 px-6 text-left whitespace-nowrap">
                        <div className="flex items-center">
                          <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                            <img alt="rain" className='w-3' />
                          </div>
                          <span className="font-medium">Vehicle Photograph</span>
                        </div>
                      </td>
                      <td className="py-3 px-6">
                        <div className="flex items-center justify-center font-semibold text-sm">
                          <div className="form-group col-span-4 md:col-span-1 md:px-0">
                            <input {...formik.getFieldProps('vehiclePhoto')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                            {/* <span className="text-red-600 absolute text-xs">{formik.touched.holdingNoDoc && formik.errors.holdingNoDoc ? formik.errors.holdingNoDoc : null}</span> */}
                          </div>
                        </div>
                      </td>
                      <td className="py-3 px-6 text-center">
                        <div className="flex items-center justify-center font-semibold text-sm">
                          <img src={vehiclePhotoDocFilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                        </div>
                      </td>
                    </tr>
                    {/* Owner Book*/}
                    <tr className="border-b border-gray-200 ">
                      <td className="py-3 px-6 text-left whitespace-nowrap">
                        <div className="flex items-center">
                          <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                            <img alt="rain" className='w-3' />
                          </div>
                          <span className="font-medium">Owner Book</span>
                        </div>
                      </td>
                      <td className="py-3 px-6">
                        <div className="flex items-center justify-center font-semibold text-sm">
                          <div className="form-group col-span-4 md:col-span-1 md:px-0">
                            <input {...formik.getFieldProps('ownerBook')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                            {/* <span className="text-red-600 absolute text-xs">{formik.touched.photoWithGps && formik.errors.photoWithGps ? formik.errors.photoWithGps : null}</span> */}
                          </div>
                        </div>
                      </td>
                      <td className="py-3 px-6 text-center">
                        <div className="flex items-center justify-center font-semibold text-sm">
                          <img src={ownerBookDocFilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                        </div>
                      </td>
                    </tr>
                    {/*  GST Document*/}
                    <tr className="border-b border-gray-200 ">
                      <td className="py-3 px-6 text-left whitespace-nowrap">
                        <div className="flex items-center">
                          <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                            <img alt="rain" className='w-3' />
                          </div>
                          <span className="font-medium"> GST Document</span>
                        </div>
                      </td>
                      <td className="py-3 px-6">
                        <div className="flex items-center justify-center font-semibold text-sm">
                          <div className="form-group col-span-4 md:col-span-1 md:px-0">
                            <input {...formik.getFieldProps('gstDocPhoto')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                            {/* <span className="text-red-600 absolute text-xs">{formik.touched.gstDocPhoto && formik.errors.gstDocPhoto ? formik.errors.gstDocPhoto : null}</span> */}
                          </div>
                        </div>
                      </td>
                      <td className="py-3 px-6 text-center">
                        <div className="flex items-center justify-center font-semibold text-sm">
                          <img src={gstDocPhotoDocFilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                        </div>
                      </td>
                    </tr>
                    {/*  Driving License*/}
                    <tr className="border-b border-gray-200 ">
                      <td className="py-3 px-6 text-left whitespace-nowrap">
                        <div className="flex items-center">
                          <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                            <img alt="rain" className='w-3' />
                          </div>
                          <span className="font-medium"> Driving License</span>
                        </div>
                      </td>
                      <td className="py-3 px-6">
                        <div className="flex items-center justify-center font-semibold text-sm">
                          <div className="form-group col-span-4 md:col-span-1 md:px-0">
                            <input {...formik.getFieldProps('drivingLicense')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                            {/* <span className="text-red-600 absolute text-xs">{formik.touched.proceedingPhoto1 && formik.errors.proceedingPhoto1 ? formik.errors.proceedingPhoto1 : null}</span> */}
                          </div>
                        </div>
                      </td>
                      <td className="py-3 px-6 text-center">
                        <div className="flex items-center justify-center font-semibold text-sm">
                          <img src={drivingLicenseDocFilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                        </div>
                      </td>
                    </tr>
                    {/* Insurance Photo*/}
                    <tr className="border-b border-gray-200 ">
                      <td className="py-3 px-6 text-left whitespace-nowrap">
                        <div className="flex items-center">
                          <div className="mr-2 bg-white shadow-lg rounded-full p-2">
                            <img alt="rain" className='w-3' />
                          </div>
                          <span className="font-medium">Insurance Photo</span>
                        </div>
                      </td>
                      <td className="py-3 px-6">
                        <div className="flex items-center justify-center font-semibold text-sm">
                          <div className="form-group col-span-4 md:col-span-1 md:px-0">
                            <input {...formik.getFieldProps('insurancePhoto')} type='file' className="form-control block w-full px-3 py-1.5 text-base md:text-xs font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none cursor-pointer shadow-md w-36" webkitdirectory />
                            {/* <span className="text-red-600 absolute text-xs">{formik.touched.proceedingPhoto2 && formik.errors.proceedingPhoto2 ? formik.errors.proceedingPhoto2 : null}</span> */}
                          </div>
                        </div>
                      </td>
                      <td className="py-3 px-6 text-center">
                        <div className="flex items-center justify-center font-semibold text-sm">
                          <img src={insurancePhotoFilePreview} alt="previewImage" className='w-16 cursor-pointer' />
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div className="grid grid-cols-12 w-full">
                <div className='md:pl-0 col-span-4'>
                  <button type="button" className=" px-6 py-2.5 bg-blue-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out" onClick={() => props.backFun(2)}>back</button>
                </div>
                <div className='md:px-4 text-center col-span-4'>
                  <button type='button' className=" px-6 py-2.5 bg-green-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out">Upload Document</button>
                </div>
                <div className='md:pl-10 text-right col-span-4'>
                  <button type='submit' className=" px-6 py-2.5 bg-green-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out">Submit</button>
                </div>

              </div>

            </div>
          </div>
        </div>

      </form>
    </>
  )
}

export default MovableVehicleDocForm