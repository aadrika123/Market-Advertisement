import React, { useEffect, useState } from 'react'
import { useFormik } from 'formik';
import SelfAdvrtInformationScreen from '../SelfAdvertisement/SelfAdvrtInformationScreen';
import AdvertisementApiList from '../../../Compnents/AdvertisementApiList';
import ApiHeader from '../../../../src/Compnents/ApiHeader'
import axios from 'axios';
import Modal from 'react-modal';
import BackButton from '../BackButton';
import FindTradeLicense from '../FindTradeLicense';

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
Modal.setAppElement('#root');

function MovableVehicleForm(props) {
  const { api_getAdvertMasterData, api_getUlbList, api_getTradeLicenseDetails } = AdvertisementApiList()
  const [masterData, setmasterData] = useState()
  const [ulbList, setulbList] = useState()
  const [reviewIdName, setreviewIdName] = useState({})
  const [storeUlbValue, setstoreUlbValue] = useState()

  const [licenseId, setlicenseId] = useState()
  const [liceneData, setlicenseData] = useState()
  const [liceneDetails, setlicenseDetails] = useState()

  const [modalIsOpen, setIsOpen] = useState(true);
  const openModal = () => setIsOpen(true)
  const closeModal = () => setIsOpen(false)
  const afterOpenModal = () => { }

  let labelStyle = "mt-6 -ml-7 text-xs text-gray-600"
  let inputStyle = "text-xs rounded-md shadow-md px-1.5 py-1 w-[10rem] md:w-[13rem] h-6 md:h-8  mt-5 -ml-2 "

  const initialValues = {
    ulb: '',
    licenseFrom: '',
    licenseTo: '',
    applicantName: liceneDetails?.applicant_name,
    fatherName: '',
    email: '',
    residenceAddress: '',
    residenceWardNo: '',
    permanentAddress: '',
    permanentWardNo: '',
    mobileNo: liceneDetails?.mobile,
    aadharNo: '',
    entityName: liceneDetails?.entity_name,
    tradeLicenseNo: liceneData?.licenseDataById,
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
  }

  const formik = useFormik({
    initialValues: initialValues,
    enableReinitialize: true,

    onSubmit: values => {
      // alert(JSON.stringify(values, null, 2));
      console.log("movable vehicle", values, reviewIdName)
      props.collectFormDataFun('movableVehicle', values, reviewIdName)
      props?.nextFun(1)
    },
  });

  const handleChange = (e) => {
    let name = e.target.name
    let value = e.target.value

    { name == 'tradeLicenseNo' && formik.setFieldValue("tradeLicenseNo", formik.values.tradeLicenseNo) }

    { name == 'ulb' && getMasterDataFun(value) }
    { name == 'ulb' && setstoreUlbValue(value) }
    console.log("ulb id...", value)

    // {****** collection names By Id ******}//
    if (e.target.type == 'select-one') {
      setreviewIdName({ ...reviewIdName, [name]: e.target[e.target.selectedIndex].text })
    }
    else {
      setreviewIdName({ ...reviewIdName, [name]: value })
    }
  }

  console.log("review name.. in form...", reviewIdName)
  ///////////{*** GETTING ULB DATA***}/////////
  useEffect(() => {
    getUlbListData()
  }, [])
  const getUlbListData = () => {
    axios.get('http://192.168.0.16:8000/api/get-all-ulb', ApiHeader())
      .then(function (response) {
        console.log('ulb list in self advertisement', response.data.data)
        setulbList(response.data.data)
      })
      .catch(function (error) {
        console.log('errorrr.... ', error);
      })
  }
  console.log("ulb list", ulbList)

  ///////////{*** GETTING MASTER DATA***}/////////
  const getMasterDataFun = (ulbId) => {
    const requestBody = {
      // ulbId: ulbId,
      ulbId: 1,
      deviceId: "selfAdvert",
    }
    axios.post(`${api_getAdvertMasterData}`, requestBody, ApiHeader())
      .then(function (response) {
        console.log('master data for self advertisement', response.data.data)
        setmasterData(response.data.data)
      })
      .catch(function (error) {
        console.log('errorrr.... ', error);
      })
  }


  ///////////{*** COLLECTING TRADE LICENSE DATA***}/////////
  const collectData = (key, formData) => {
    console.log('trade license data.......11', formData)
    setlicenseData({ ...liceneData, [key]: formData })
  }


  ///////////{***DETAILS BY TRADE LICENSE NO. ***}/////////
  useEffect(() => {
    getTradeLicenseDetails()
  }, [liceneData?.licenseDataById])

  const getTradeLicenseDetails = () => {
    props.showLoader(true);
    const requestBody = {
      license_no: liceneData?.licenseDataById,
      // license_no: "919191",
      // deviceId: "selfAdvert",
    }
    axios.post(`${api_getTradeLicenseDetails}`, requestBody, ApiHeader())
      .then(function (response) {
        console.log('trade license details...12', response)
        setlicenseDetails(response.data.data)
        setTimeout(() => {
          props.showLoader(false);
        }, 500);
      })
      .catch(function (error) {
        console.log('errorrr.... ', error);
        setTimeout(() => {
          props.showLoader(false);
        }, 500);

      })
  }

  console.log(" master data...", masterData)
  console.log(" License full data in state", liceneData?.licenseDataById)
  console.log(" trade License details", liceneDetails)

  return (
    <>
      <Modal
        isOpen={modalIsOpen}
        onAfterOpen={afterOpenModal}
        onRequestClose={closeModal}
        style={customStyles}
        contentLabel="Example Modal"
        shouldCloseOnOverlayClick={false}

      >

        <div class=" rounded-lg shadow-xl border-2 border-gray-50 mx-auto px-0" style={{ 'width': '80vw', 'height': '100%' }}>
          <FindTradeLicense showLoader={props.showLoader} closeFun={closeModal} collectDataFun={collectData} />
        </div>

      </Modal>

      <form onSubmit={formik.handleSubmit} onChange={handleChange}>
        <div>
          <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 w-10/12  container mx-auto -mt-4'>
            <div className='col-span-6 '>
              <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 w-10/12  container mx-auto'>
                <div className=' col-span-3'>
                  <p className={`mt-5 text-gray-600 text-sm -ml-8`}>Trade License No.</p>
                </div>
                <div className=' col-span-3'>
                  <input type="text" name='tradeLicenseNo' placeholder='' className={`h-6 md:h-8 w-[10rem] md:w-[13rem]  mt-4 bg-white rounded-l leading-5 shadow-md text-xs px-2 bg-gray-50`} disabled
                    onChange={formik.handleChange}
                    value={formik.values.tradeLicenseNo}
                  />
                </div>
              </div>
            </div>
            <div className='col-span-6  -ml-0 md:-ml-24 lg:-ml-20'>
              <div className='grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 w-10/12  container mx-auto'>
                <div className=' col-span-3'>
                  <p className={`mt-6 text-gray-600 text-sm -ml-14`}>Urban Local Bodies (ULB)</p>
                </div>
                <div className=' col-span-3'>
                  <select className={`h-6 md:h-8 w-[10rem] md:w-[13rem]  mt-4 bg-white rounded-l leading-5 shadow-md text-xs px-2 bg-gray-50`} {...formik.getFieldProps('ulb')} >
                    <option>select </option>
                    {ulbList?.map((items) => (
                      <option value={items?.id}>{items?.ulb_name}</option>
                    ))}
                  </select>
                </div>
              </div>
            </div>
          </div>

          {storeUlbValue == undefined &&
            <div className='p-6 mt-8'>
              <h1 className='text-center text-gray-500 text-2xl'>select <span className='font-bold'>ULB</span> to proceed further with the application</h1>
              <img src='https://img.freepik.com/free-vector/dropdown-menu-concept-illustration_114360-4047.jpg?w=740&t=st=1674129515~exp=1674130115~hmac=a941e2b3ea5a5a49d3bdd425e263fffd9f6bdebe70c2ac93b3d2eeb6b02017dc' className='h-96 mx-auto mt-8' />
            </div>
          }

          {storeUlbValue != undefined &&
            <div>
              <div className=' grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-4 container  mx-auto pb-8 p-2 mt-2'>
                <div className='col-span-4 p-1 border border-dashed border-violet-800'>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle} `}> License From <span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="date" name='licenseFrom' placeholder='' className={`${inputStyle}`}
                        onChange={formik.handleChange}
                        value={formik.values.licenseFrom}
                      />

                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle}`}> License To <span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="date" name='licenseTo' placeholder='' className={`${inputStyle}`}
                        onChange={formik.handleChange}
                        value={formik.values.licenseTo}
                      />

                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle}`}> Applicant<span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="text" name='applicantName' placeholder='' className={`${inputStyle}`} disabled
                        onChange={formik.handleChange}
                        value={formik.values.applicantName}
                      />
                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle}`}> Father Name<span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="text" name='fatherName' placeholder='' className={`${inputStyle}`}
                        onChange={formik.handleChange}
                        value={formik.values.fatherName}
                      />
                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle}`}>Residence Address<span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="text" name='residenceAddress' placeholder='' className={`${inputStyle}`}
                        onChange={formik.handleChange}
                        value={formik.values.residenceAddress}
                      />
                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle}`}>Ward No<span className='text-red-600'>*</span></p>
                    </div>
                    <div className='col-span-2'>
                      <select {...formik.getFieldProps('residenceWardNo')} className={`${inputStyle} bg-white`} >
                        <option>select one</option>
                        {masterData?.wards?.map((items) => (
                          <option value={items?.id}>{items?.ward_name}</option>
                        ))}
                      </select>
                    </div>
                  </div>

                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle}`}>Permanent Address <span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="text" name='permanentAddress' placeholder='' className={`${inputStyle}`}
                        onChange={formik.handleChange}
                        value={formik.values.permanentAddress}
                      />
                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle}`}>Ward No <span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <select  {...formik.getFieldProps('permanentWardNo')} className={`${inputStyle} bg-white`} >
                        <option>select one</option>
                        {masterData?.wards?.map((items) => (
                          <option value={items?.id}>{items?.ward_name}</option>
                        ))}
                      </select>
                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle}`}>Mobile<span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="text" name='mobileNo' placeholder='' className={`${inputStyle}`} disabled
                        onChange={formik.handleChange}
                        value={formik.values.mobileNo}
                      />
                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle}`}>Aadhar No <span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="text" name='aadharNo' placeholder='' className={`${inputStyle}`}
                        onChange={formik.handleChange}
                        value={formik.values.aadharNo}
                      />
                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle}`}>E-mail <span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="text" name='email' placeholder='' className={`${inputStyle}`}
                        onChange={formik.handleChange}
                        value={formik.values.email}
                      />
                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8 mb-2'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle}`}>Entity Name<span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="text" name='entityName' placeholder='' className={`${inputStyle}`} disabled
                        onChange={formik.handleChange}
                        value={formik.values.entityName}
                      />
                    </div>
                  </div>
                </div>
                <div className='col-span-4 p-1 border border-dashed border-violet-800'>

                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle}`}>GST No <span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="text" name='gstNo' placeholder='' className={`${inputStyle} flex-1`}
                        onChange={formik.handleChange}
                        value={formik.values.gstNo}
                      />
                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle} flex-1`}>Vehicle No<span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="text" name='vehicleNo' placeholder='' className={`${inputStyle} flex-1`}
                        onChange={formik.handleChange}
                        value={formik.values.vehicleNo}
                      />

                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle} flex-1`}>Vehicle Name <span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="text" name='vehicleName' placeholder='' className={`${inputStyle} flex-1`}
                        onChange={formik.handleChange}
                        value={formik.values.vehicleName}
                      />
                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle} flex-1 `}>Vehicle Type<span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <select {...formik.getFieldProps('vehicleType')} className={`${inputStyle} flex-1 bg-white`} >
                        <option>select one</option>
                        {masterData?.paramCategories?.VehicleTypes?.map((items) => (
                          <option value={items?.id}>{items?.string_parameter}</option>
                        ))}
                      </select>
                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle} flex-1`}>Brand in Vehicle <span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="text" name='brandDisplayedInVehicle' placeholder='' className={`${inputStyle} flex-1`}
                        onChange={formik.handleChange}
                        value={formik.values.brandDisplayedInVehicle}
                      />
                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle} flex-1`}>Front Area(Sq ft) <span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="text" name='frontArea' placeholder='' className={`${inputStyle} flex-1`}
                        onChange={formik.handleChange}
                        value={formik.values.frontArea}
                      />
                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle} flex-1`}>Rear Area(Sq ft) <span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="text" name='rearArea' placeholder='' className={`${inputStyle} flex-1`}
                        onChange={formik.handleChange}
                        value={formik.values.rearArea}
                      />
                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle} flex-1`}>Side 1 Area(Sq ft)<span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="text" name='sideOneArea' placeholder='' className={`${inputStyle} flex-1`}
                        onChange={formik.handleChange}
                        value={formik.values.sideOneArea}
                      />
                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle} flex-1`}>Top Area(Sq ft)<span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <input type="text" name='topArea' placeholder='' className={`${inputStyle} flex-1 `}
                        onChange={formik.handleChange}
                        value={formik.values.topArea}
                      />
                    </div>
                  </div>
                  <div className='grid grid-cols-3 md:grid-cols-3 lg:grid-cols-3 ml-8'>
                    <div className='col-span-1'>
                      <p className={`${labelStyle} flex-1 `}>Display Type<span className='text-red-600'> *</span></p>
                    </div>
                    <div className='col-span-2'>
                      <select {...formik.getFieldProps('displayType')} className={`${inputStyle} flex-1 bg-white`} >
                        <option>select one</option>
                        {masterData?.paramCategories?.DisplayType?.map((items) => (
                          <option value={items?.id}>{items?.string_parameter}</option>
                        ))}
                      </select>

                    </div>
                  </div>
                </div>
                <div className='col-span-4'>
                  <div className='-mt-20'>
                    <SelfAdvrtInformationScreen />
                  </div>
                </div>
              </div>
              <div className=' '>
                <div className='float-right p-2'>
                  <button type="submit" class="py-2 px-4 text-xs inline-block text-center mb-3 rounded leading-5 text-gray-100 bg-green-500 border border-green-500 hover:text-white hover:bg-green-600 hover:ring-0 hover:border-green-600 focus:bg-green-600 focus:border-green-600 focus:outline-none focus:ring-0">Save & Next</button>

                </div>
              </div>
            </div>
          }
        </div>
      </form>
    </>
  )
}

export default MovableVehicleForm