import {
  createContext,
  PropsWithChildren,
  useContext,
  useMemo,
  useState,
} from "react";
import {
  addFolderToParentFolder,
  recursivelyRemoveFolder,
} from "./state-helper";

export type DataNode = {
  id: number;
  name: string;
  children: DataNode[];
};

type TreeState = {
  folders: DataNode[];
  openFolders: number[];
};

type TreeContext = TreeState & {
  setFolders: (folders: TreeState["folders"]) => void;
  setOpenFolders: (openFolders: TreeState["openFolders"]) => void;
  setFolderOpen: (folderId: number) => void;
  setFolderClosed: (folderId: number) => void;
  removeFolder: (folderId: number) => void;
  addFolder: (folderName: string, parentFolderid?: number) => void;
};

const initialState: TreeState = {
  folders: [],
  openFolders: [],
};

const TreeContext = createContext<TreeContext>({
  folders: [],
  openFolders: [],
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  setFolders: (_folders: TreeState["folders"]): void => {
    throw new Error("Function not implemented.");
  },
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  setOpenFolders: (_openFolders: TreeState["openFolders"]): void => {
    throw new Error("Function not implemented.");
  },
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  setFolderOpen: (_folderId: number): void => {
    throw new Error("Function not implemented.");
  },
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  setFolderClosed: (_folderId: number): void => {
    throw new Error("Function not implemented.");
  },
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  removeFolder: (_folderId: number): void => {
    throw new Error("Function not implemented.");
  },
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  addFolder: (_folderName: string, _parentFolderid?: number): void => {
    throw new Error("Function not implemented.");
  },
});

export const TreeState = ({ children }: PropsWithChildren) => {
  const [treeState, setTreeState] = useState<TreeState>(initialState);

  const setFolders = (folders: TreeState["folders"]) => {
    setTreeState((oldState) => ({ ...oldState, folders }));
  };

  const setOpenFolders = (openFolders: TreeState["openFolders"]) => {
    setTreeState((oldState) => ({ ...oldState, openFolders }));
  };

  const setFolderOpen = (folderId: number) => {
    setTreeState((oldState) => ({
      ...oldState,
      openFolders: [...new Set(oldState.openFolders).add(folderId)],
    }));
  };

  const setFolderClosed = (folderId: number) => {
    setTreeState((oldState) => ({
      ...oldState,
      openFolders: [...oldState.openFolders].filter(
        (folder) => folder !== folderId
      ),
    }));
  };

  const removeFolder = (folderId: number) => {
    setTreeState((oldState) => ({
      ...oldState,
      folders: recursivelyRemoveFolder(oldState.folders, folderId),
      openFolders: [...oldState.openFolders].filter(
        (folder) => folder !== folderId
      ),
    }));
  };

  const addFolder = (folderName: string, parentFolderid?: number) => {
    setTreeState((oldState) => ({
      ...oldState,
      folders: addFolderToParentFolder(
        oldState.folders,
        folderName,
        parentFolderid
      ),
      openFolders: parentFolderid
        ? [...new Set(oldState.openFolders).add(parentFolderid)]
        : oldState.openFolders,
    }));
  };

  const value = useMemo(
    () => ({
      ...treeState,
      setFolders,
      setOpenFolders,
      setFolderOpen,
      setFolderClosed,
      removeFolder,
      addFolder,
    }),
    [treeState]
  );

  return <TreeContext.Provider value={value}>{children}</TreeContext.Provider>;
};

// eslint-disable-next-line react-refresh/only-export-components
export const useTreeState = () => useContext(TreeContext);
