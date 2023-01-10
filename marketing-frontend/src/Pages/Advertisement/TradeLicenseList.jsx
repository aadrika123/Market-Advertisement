import React from 'react'

function TradeLicenseList() {

    const [dummyList, setdummyList] = useState([
        {
            id: "1",
            tradeLicenseNo: "TRADE123456",
            holdingNo: "HOL / 9 / 2023",
            ownerName: "Anant",
            licenseIssueDate: "09 / 01 / 2023",
        },
        {
            id: "2",
            tradeLicenseNo: "TRADE123497",
            holdingNo: "HOL / 10 / 2023",
            ownerName: "Dev",
            licenseIssueDate: "10 / 01 / 2023",
        },
        {
            id: "3",
            tradeLicenseNo: "TRADE123487",
            holdingNo: "HOL / 11 / 2023",
            ownerName: "Applicant",
            licenseIssueDate: "11 / 01 / 2023",
        },

    ])



    let labelStyle = "mt-6 -ml-6 text-xs text-gray-600"
    let inputStyle = "text-xs rounded leading-5 shadow-md px-1.5 py-1 w-[10rem] md:w-[13rem] h-6 md:h-8  mt-5 -ml-2 "
    return (
        <>

            <div className='mt-5 bg-white p-2 w-11/12 mx-auto'>
                <h1 className='ml-10 p-2 font-semibold text-gray-700'>Your Licenses</h1>
                <table class="table-auto text-slate-700 w-11/12 mx-auto border ">
                    <thead>
                        <tr className="bg-violet-200 text-gray-600 text-xs h-8 hover:bg-violet-200 uppercase">
                            <th>Trade License No.</th>
                            <th>Holding No.</th>
                            <th>Owner Name</th>
                            <th>License Issue Date</th>
                            <th>select</th>
                        </tr>
                    </thead>
                    <tbody>
                        {dummyList?.map((items) => (
                            <tr className='border-t-2 bg-white hover:bg-violet-200 text-sm hover:shadow-lg text-center  '>
                                <td className={`${labelStyle}`}>{items?.tradeLicenseNo}</td>
                                <td className={`${labelStyle}`} >{items?.holdingNo}</td>
                                <td className={`${labelStyle}`}>{items?.ownerName}</td>
                                <td className={`${labelStyle}`}>{items?.licenseIssueDate}</td>
                                {/* <td><input type='checkbox' name='check' onChange={() => getLicenseData(items.id)} /></td> */}
                                <td><input type='checkbox' name='check' onChange={() => getLicenseData(items?.tradeLicenseNo)} /></td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                <button className='text-xs mt-2 ml-12 bg-indigo-500 hover:bg-indigo-600 px-2 py-1 text-white rounded leading-5 '>Not In List</button>
            </div>
        </>
    )
}

export default TradeLicenseList