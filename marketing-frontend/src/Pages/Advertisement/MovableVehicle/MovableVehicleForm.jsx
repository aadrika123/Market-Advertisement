import React from 'react'
import { useFormik } from 'formik';

function MovableVehicleForm(props) {


  const formik = useFormik({
    initialValues: {
      licenseFrom: '',
      licenseTo: '',
      applicantName: '',
      fatherName: '',
      email: '',
      residenceAddress: '',
      residenceWardNo: '',
      permanentAddress: '',
      permanentWardNo: '',
      mobileNo: '',
      aadharNo: '',
      entityName: '',
      tradeLicenseNo: '',
      gstNo: '',
      vehicleNo: '',
      vehicleName: '',
      vehicleType: '',
      brandDisplayedInVehicle: '',
      frontArea: '',
      rearArea: '',
      sideOneArea: '',
      topArea: '',
      displayType: '',


    },
    onSubmit: values => {
      alert(JSON.stringify(values, null, 2));
      console.log("movable vehicle", values)
      props?.nextFun(1)
    },
  });

  let labelStyle = "mt-6 pl-1 text-md text-gray-600"
  let inputStyle = "border shadow-md px-1.5 py-1 rounded-md w-[13rem]"

  return (
    <>
      <form onSubmit={formik.handleSubmit}>
        <div class="container mx-auto bg-white rounded-lg  shadow-md p-16 mt-3 overflow-y-scroll mb-28 ">
          <div className=' grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 -mt-8 '>
            <div className='flex flex-row'>
              <img src='https://cdn-icons-png.flaticon.com/512/2518/2518048.png' className='h-6 mt-2 opacity-80' />
              <h1 className='text-2xl ml-2 text-gray-600 font-sans '>Movable Vehicle</h1>
            </div>
            <h1 className='text-sm ml-9 text-gray-400 font-sans'>You Can Get License To Advertise Your Business Name On Your Shop</h1>
          </div>
          <div className='bg-gray-50 p-6 rounded-lg mt-2'>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-1 ">
              <div className='px-1'>
                <p className={`${labelStyle}`}> License From <span className='text-red-600'> *</span></p>
                <select className={`${inputStyle} bg-white`} {...formik.getFieldProps('licenseFrom')} >
                  <option>select one</option>
                  <option>1</option>
                  <option>1</option>
                  <option>1</option>
                </select>
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}> License To <span className='text-red-600'> *</span></p>
                <select className={`${inputStyle} bg-white`} {...formik.getFieldProps('licenseTo')} >
                  <option>select one</option>
                  <option>1</option>
                  <option>1</option>
                  <option>1</option>
                </select>
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}>Applicant <span className='text-red-600'> *</span></p>
                <input type="text" name='applicantName' placeholder='' className={`${inputStyle}`}
                  onChange={formik.handleChange}
                  value={formik.values.applicantName}
                />
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}>Father <span className='text-red-600'> *</span></p>
                <input type="text" name='fatherName' placeholder='' className={`${inputStyle}`}
                  onChange={formik.handleChange}
                  value={formik.values.fatherName}
                />
              </div>

            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-3 ">
              <div className='px-1'>
                <p className={`${labelStyle}`}>Residence Address <span className='text-red-600'> *</span></p>
                <input type="text" name='residenceAddress' placeholder='' className={`${inputStyle}`}
                  onChange={formik.handleChange}
                  value={formik.values.residenceAddress}
                />
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}>Ward No <span className='text-red-600'> *</span></p>
                <select {...formik.getFieldProps('residenceWardNo')} className={`${inputStyle} bg-white`} >
                  <option>select one</option>
                  <option>1</option>
                  <option>1</option>
                  <option>1</option>
                </select>
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}>Permanent Address <span className='text-red-600'> *</span></p>
                <input type="text" name='permanentAddress' placeholder='' className={`${inputStyle}`}
                  onChange={formik.handleChange}
                  value={formik.values.permanentAddress}
                />
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}>Ward No <span className='text-red-600'> *</span></p>
                <select  {...formik.getFieldProps('permanentWardNo')} className={`${inputStyle} bg-white`} >
                  <option>select one</option>
                  <option>1</option>
                  <option>1</option>
                  <option>1</option>
                </select>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-3 ">
              <div className='px-1'>
                <p className={`${labelStyle}`}>Mobile <span className='text-red-600'> *</span></p>
                <input type="text" name='mobileNo' placeholder='' className={`${inputStyle} `}
                  onChange={formik.handleChange}
                  value={formik.values.mobileNo}
                />
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}>Aadhar No <span className='text-red-600'> *</span></p>
                <input type="text" name='aadharNo' placeholder='' className={`${inputStyle}`}
                  onChange={formik.handleChange}
                  value={formik.values.aadharNo}
                />
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}>E-mail <span className='text-red-600'> *</span></p>
                <input type="text" name='email' placeholder='' className={`${inputStyle}`}
                  onChange={formik.handleChange}
                  value={formik.values.email}
                />
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}>Entity Name<span className='text-red-600'> *</span></p>
                <input type="text" name='entityName' placeholder='' className={`${inputStyle}`}
                  onChange={formik.handleChange}
                  value={formik.values.entityName}
                />
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-3 ">
              <div className='px-1'>
                <p className={`${labelStyle}`}>GST No <span className='text-red-600'> *</span></p>
                <select type="text" name='gstNo' placeholder='' className={`${inputStyle} bg-white`} >
                  <option>select one</option>
                  <option>1</option>
                  <option>1</option>
                  <option>1</option>
                </select>
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}>Vehicle No<span className='text-red-600'> *</span></p>
                <select {...formik.getFieldProps('vehicleNo')} className={`${inputStyle} bg-white`} >
                  <option>select one</option>
                  <option>1</option>
                  <option>1</option>
                  <option>1</option>
                </select>
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}>Vehicle Name <span className='text-red-600'> *</span></p>
                <input type="text" name='vehicleName' placeholder='' className={`${inputStyle}`}
                  onChange={formik.handleChange}
                  value={formik.values.brandDisplayName}
                />
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}>Vehicle Type .<span className='text-red-600'> *</span></p>
                <input type="text" name='vehicleType' placeholder='' className={`${inputStyle}`}
                  onChange={formik.handleChange}
                  value={formik.values.holdingNo}
                />
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-3 ">
              <div className='px-1'>
                <p className={`${labelStyle}`}>Brand Displayed in Vehicle <span className='text-red-600'> *</span></p>
                <input type="text" name='brandDisplayedInVehicle' placeholder='' className={`${inputStyle}`}
                  onChange={formik.handleChange}
                  value={formik.values.tradeLicenseNo}
                />
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}>Front Area(Sq ft) <span className='text-red-600'> *</span></p>
                <input type="text" name='frontArea' placeholder='' className={`${inputStyle}`}
                  onChange={formik.handleChange}
                  value={formik.values.gstNo}
                />
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}>Rear Area(Sq ft) <span className='text-red-600'> *</span></p>
                <input type="text" name='rearArea' placeholder='' className={`${inputStyle}`}
                  onChange={formik.handleChange}
                  value={formik.values.displayArea}
                />
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}>Side 1 Area(Sq ft)<span className='text-red-600'> *</span></p>
                <input type="text" name='sideOneArea' placeholder='' className={`${inputStyle}`}
                  onChange={formik.handleChange}
                  value={formik.values.displayType}
                />
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-3 ">

              <div className='px-1'>
                <p className={`${labelStyle}`}>Top Area(Sq ft)   <span className='text-red-600'> *</span></p>
                <input type="text" name='topArea' placeholder='' className={`${inputStyle}`}
                  onChange={formik.handleChange}
                  value={formik.values.longitude}
                />
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}>Display Type<span className='text-red-600'> *</span></p>
                <input type="text" name='displayType' placeholder='' className={`${inputStyle}`}
                  onChange={formik.handleChange}
                  value={formik.values.latitude}
                />
              </div>
              <div className='px-1'>
                <p className={`${labelStyle}`}>Trade License No<span className='text-red-600'> *</span></p>
                <input type="text" name='tradeLicenseNo' placeholder='' className={`${inputStyle}`}
                  onChange={formik.handleChange}
                  value={formik.values.entityAddress}
                />
              </div>
            </div>
          </div>
          <div className=' '>
            <div className='float-right p-4'>
              <button type='submit' className='bg-green-600 w-36 h-9 font-semibold shadow-md text-gray-100 hover:bg-green-600' >SAVE & NEXT</button>
            </div>
          </div>
        </div>
      </form>
    </>
  )
}

export default MovableVehicleForm